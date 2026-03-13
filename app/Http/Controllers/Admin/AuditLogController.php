<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['module','action','user','date_from','date_to','search']);

        $q = AuditLog::with('user')->latest();

        if (!empty($filters['module']))    $q->where('module', $filters['module']);
        if (!empty($filters['action']))    $q->where('action', 'like', '%'.$filters['action'].'%');
        if (!empty($filters['user']))      $q->where('user_name', 'like', '%'.$filters['user'].'%');
        if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('created_at', '<=', $filters['date_to']);
        if (!empty($filters['search']))    $q->where('description', 'like', '%'.$filters['search'].'%');

        $logs    = $q->paginate(50);
        $modules = AuditLog::distinct()->pluck('module')->filter()->sort()->values();
        $stats   = [
            'today'   => AuditLog::whereDate('created_at', today())->count(),
            'week'    => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'month'   => AuditLog::whereMonth('created_at', now()->month)->count(),
            'total'   => AuditLog::count(),
        ];

        return view('admin.audit.index', compact('logs','filters','modules','stats'));
    }

    public function show(AuditLog $log)
    {
        return view('admin.audit.show', compact('log'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['module','date_from','date_to']);
        $q = AuditLog::with('user')->latest();
        if (!empty($filters['module']))    $q->where('module', $filters['module']);
        if (!empty($filters['date_from'])) $q->whereDate('created_at', '>=', $filters['date_from']);
        if (!empty($filters['date_to']))   $q->whereDate('created_at', '<=', $filters['date_to']);

        $logs = $q->limit(10000)->get();
        $csv  = "ID,Action,Module,User,Subject,IP,Description,Date\n";
        foreach ($logs as $l) {
            $csv .= implode(',', [
                $l->id,
                '"'.$l->action.'"',
                '"'.($l->module ?? '—').'"',
                '"'.($l->user_name ?? '—').'"',
                '"'.($l->subject_label ?? '—').'"',
                $l->ip_address,
                '"'.str_replace('"',"'", $l->description ?? '').'"',
                $l->created_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-'.now()->format('Y-m-d').'.csv"',
        ]);
    }
}