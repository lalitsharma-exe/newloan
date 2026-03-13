<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $userId        = auth('admin')->id();
        $notifications = Notification::where('user_id', $userId)
                            ->latest()->paginate(30);
        $unreadCount   = Notification::where('user_id', $userId)
                            ->where('is_read', false)->count();
        return view('admin.notifications.index', compact('notifications','unreadCount'));
    }

    public function markRead($id)
    {
        Notification::where('id', $id)
            ->where('user_id', auth('admin')->id())
            ->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth('admin')->id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        Notification::where('id', $id)
            ->where('user_id', auth('admin')->id())
            ->delete();
        return back()->with('success', 'Notification deleted.');
    }
}