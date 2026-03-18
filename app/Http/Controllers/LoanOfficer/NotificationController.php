<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth('officer')->id())
            ->latest()->paginate(20);
        return view('officer.notifications.index', compact('notifications'));
    }

    public function markRead(string $id)
    {
        Notification::where('id', $id)->where('user_id', auth('officer')->id())->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth('officer')->id())->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
