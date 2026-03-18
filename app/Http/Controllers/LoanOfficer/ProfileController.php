<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};

class ProfileController extends Controller
{
    public function index()
    {
        return view('officer.profile.index', ['user' => auth('officer')->user()]);
    }

    public function update(Request $request)
    {
        $user = auth('officer')->user();
        $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'nullable|string|max:30|unique:users,phone,' . $user->id,
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
        $user = auth('officer')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed successfully.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = auth('officer')->user();
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }
        $user->update(['profile_photo' => $request->file('photo')->store('profile-photos', 'public')]);
        return back()->with('success', 'Profile photo updated.');
    }
}
