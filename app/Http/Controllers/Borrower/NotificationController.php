<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index() {
        $notifications = Notification::where('user_id', auth('borrower')->id())->latest()->paginate(20);
        return view('borrower.notifications.index', compact('notifications'));
    }
    public function markRead(string $id) {
        Notification::where('id', $id)->where('user_id', auth('borrower')->id())->update(['is_read' => true]);
        return back();
    }
    public function markAllRead() {
        Notification::where('user_id', auth('borrower')->id())->update(['is_read' => true]);
        return back()->with('success', 'All marked as read.');
    }
}
