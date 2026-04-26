<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{SmsCampaign, SmsCampaignMessage, User, Loan, AuditLog};
use App\Jobs\ProcessSmsCampaign;
use Illuminate\Http\Request;

class BulkSmsController extends Controller
{
    public function index()
    {
        $campaigns = SmsCampaign::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.bulk-sms.index', compact('campaigns'));
    }

    public function create()
    {
        $audienceCounts = [
            'all'          => User::where('role', 'borrower')->whereNotNull('phone')->count(),
            'active_loans' => User::where('role', 'borrower')->whereHas('loans', fn($q) => $q->where('status', 'active'))->count(),
            'overdue'      => User::where('role', 'borrower')->whereHas('loans', fn($q) => $q->where('status', 'overdue'))->count(),
            'defaulted'    => User::where('role', 'borrower')->whereHas('loans', fn($q) => $q->where('status', 'defaulted'))->count(),
        ];

        return view('admin.bulk-sms.create', compact('audienceCounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'message'    => 'required|string|max:480',
            'audience'   => 'required|in:all,active_loans,overdue,defaulted,custom',
            'phones'     => 'nullable|string',  // For custom audience
        ]);

        // Build recipient list based on audience
        $recipients = $this->getRecipients($request->audience, $request->phones);

        if ($recipients->isEmpty()) {
            return back()->with('error', 'No recipients found for the selected audience.')->withInput();
        }

        // Create campaign
        $campaign = SmsCampaign::create([
            'name'             => $request->name,
            'message'          => $request->message,
            'audience'         => $request->audience,
            'status'           => 'queued',
            'total_recipients' => $recipients->count(),
            'created_by'       => auth('admin')->id(),
            'queued_at'        => now(),
        ]);

        // Insert all recipient messages
        $messages = $recipients->map(fn($r) => [
            'campaign_id'    => $campaign->id,
            'phone'          => $r['phone'],
            'recipient_name' => $r['name'],
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ])->toArray();

        // Insert in chunks to handle large batches
        foreach (array_chunk($messages, 500) as $chunk) {
            SmsCampaignMessage::insert($chunk);
        }

        // Dispatch to queue
        ProcessSmsCampaign::dispatch($campaign);

        AuditLog::record(
            'sms.campaign_queued',
            "Bulk SMS campaign '{$campaign->name}' queued with {$campaign->total_recipients} recipients.",
            null, [],
            ['campaign_id' => $campaign->id, 'audience' => $request->audience]
        );

        return redirect()
            ->route('admin.bulk-sms.show', $campaign)
            ->with('success', "Campaign '{$campaign->name}' queued! {$campaign->total_recipients} messages will be sent in the background.");
    }

    public function show(SmsCampaign $campaign)
    {
        $campaign->load('creator');
        $messages = $campaign->messages()->latest()->paginate(50);

        return view('admin.bulk-sms.show', compact('campaign', 'messages'));
    }

    public function refresh(SmsCampaign $campaign)
    {
        return response()->json([
            'status'       => $campaign->status,
            'sent_count'   => $campaign->sent_count,
            'failed_count' => $campaign->failed_count,
            'progress'     => $campaign->progress,
            'total'        => $campaign->total_recipients,
        ]);
    }

    private function getRecipients(string $audience, ?string $customPhones = null)
    {
        if ($audience === 'custom' && $customPhones) {
            // Parse custom phone numbers (one per line, or comma-separated)
            $phones = collect(preg_split('/[\n,]+/', $customPhones))
                ->map(fn($p) => trim($p))
                ->filter()
                ->unique()
                ->map(function ($phone) {
                    $user = User::where('phone', 'like', "%{$phone}%")->first();
                    return [
                        'phone' => $this->formatPhone($phone),
                        'name'  => $user->name ?? 'Customer',
                    ];
                });
            return $phones;
        }

        $query = User::where('role', 'borrower')->whereNotNull('phone');

        switch ($audience) {
            case 'active_loans':
                $query->whereHas('loans', fn($q) => $q->where('status', 'active'));
                break;
            case 'overdue':
                $query->whereHas('loans', fn($q) => $q->where('status', 'overdue'));
                break;
            case 'defaulted':
                $query->whereHas('loans', fn($q) => $q->where('status', 'defaulted'));
                break;
            case 'all':
            default:
                break;
        }

        return $query->get()->map(fn($u) => [
            'phone' => $u->phone,
            'name'  => $u->name,
        ]);
    }

    private function formatPhone(?string $p): string
    {
        if (!$p) return '';
        $d = preg_replace('/[^0-9]/', '', $p);
        if (strlen($d) === 8) return '+266' . $d;
        if (strlen($d) === 11 && str_starts_with($d, '266')) return '+' . $d;
        if (str_starts_with($p, '+266')) return $p;
        return '+266' . substr($d, -8);
    }
}
