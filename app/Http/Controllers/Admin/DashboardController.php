<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller {
    public function __construct(private DashboardService $svc) {}

    public function index() {
        return view("admin.dashboard.index", [
            "stats"              => $this->svc->getStats(),
            "recentApplications" => $this->svc->getRecentApplications(10),
            "overdueLoans"       => $this->svc->getOverdueLoans(5),
            "recentPayments"     => $this->svc->getRecentPayments(5),
            "monthlyChart"       => $this->svc->getMonthlyChartData(),
        ]);
    }

    // AJAX: refresh stat cards
    public function stats() {
        return response()->json($this->svc->getStats());
    }

    // AJAX: refresh chart data
    public function chartData() {
        return response()->json($this->svc->getMonthlyChartData());
    }

    // AJAX: bell icon notification count + recent list
    public function notifications() {
        $adminId = auth('admin')->id();
        $unread  = Notification::where('user_id', $adminId)->where('is_read', false)->count();
        $recent  = Notification::where('user_id', $adminId)->latest()->limit(8)->get()
                    ->map(fn($n) => [
                        'id'      => $n->id,
                        'title'   => $n->title,
                        'body'    => $n->body,
                        'link'    => $n->link,
                        'icon'    => $n->icon,
                        'is_read' => $n->is_read,
                        'time'    => $n->created_at->diffForHumans(),
                    ]);
        return response()->json(['unread' => $unread, 'notifications' => $recent]);
    }
}

