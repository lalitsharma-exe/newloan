<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};

class ProfileController extends Controller
{
    public function index()
    {
        $user    = auth('agent')->user();
        $profile = $user->agentProfile;
        return view('agent.profile.index', compact('user', 'profile'));
    }

    public function update(Request $request)
    {
        $user = auth('agent')->user();
        $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'nullable|string|max:30|unique:users,phone,'.$user->id,
        ]);
        $user->update($request->only('name', 'phone'));
        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);
        $user = auth('agent')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = auth('agent')->user();
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo' => $path]);
        return back()->with('success', 'Profile photo updated.');
    }
}
