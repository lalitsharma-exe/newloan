<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};

use App\Models\ProfileChangeRequest;

class ProfileController extends Controller
{
    public function index() {
        $user = auth('borrower')->user();
        $user->load(['loanApplications' => fn($q) => $q->latest()->take(1)]);
        $latestRequest = ProfileChangeRequest::where('user_id', $user->id)->latest()->first();

        return view('borrower.profile.index', compact('user', 'latestRequest'));
    }

    public function requestChange(Request $request) {
        $request->validate(['requested_details' => 'required|string|max:1000']);
        ProfileChangeRequest::create([
            'user_id' => auth('borrower')->id(),
            'requested_details' => $request->requested_details,
            'status' => 'pending'
        ]);
        return back()->with('success', 'Your change request has been submitted to the admin for review.');
    }

    public function updatePersonal(Request $request) {
        return back()->with('error', 'Profile updates are restricted. Please submit a request.');
    }

    public function updateAddress(Request $request) {
        return back()->with('error', 'Profile updates are restricted. Please submit a request.');
    }

    public function updateEmployment(Request $request) {
        return back()->with('error', 'Profile updates are restricted. Please submit a request.');
    }

    public function updateBank(Request $request) {
        return back()->with('error', 'Profile updates are restricted. Please submit a request.');
    }

    public function updateNextOfKin(Request $request) {
        return back()->with('error', 'Profile updates are restricted. Please submit a request.');
    }

    public function updatePassword(Request $request) {
        $request->validate(['current_password'=>'required','password'=>'required|string|min:8|confirmed']);
        $user = auth('borrower')->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password.']);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password changed.');
    }

    public function updatePhoto(Request $request) {
        $request->validate(['photo'=>'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $user = auth('borrower')->user();
        if ($user->profile_photo) Storage::disk('public')->delete($user->profile_photo);
        $user->update(['profile_photo' => $request->file('photo')->store('profile-photos','public')]);
        return back()->with('success', 'Photo updated.');
    }
}
