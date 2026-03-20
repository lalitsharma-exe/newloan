<?php
namespace App\Http\Controllers\Borrower;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};

class ProfileController extends Controller
{
    public function index() {
        $user = auth('borrower')->user();
        $user->load(['loanApplications' => fn($q) => $q->latest()->take(1)]);
        return view('borrower.profile.index', compact('user'));
    }

    public function updatePersonal(Request $request) {
        $user = auth('borrower')->user();
        $request->validate(['name'=>'required|string|max:150','date_of_birth'=>'nullable|date','address'=>'nullable|string|max:500']);
        $user->update($request->only('name','date_of_birth','address'));
        return back()->with('success', 'Personal details updated.');
    }

    public function updateAddress(Request $request) {
        $user = auth('borrower')->user();
        $user->update(['address' => $request->address]);
        return back()->with('success', 'Address updated.');
    }

    public function updateEmployment(Request $request) {
        // Update via latest application
        $app = $request->application_id
            ? \App\Models\LoanApplication::where('user_id', auth('borrower')->id())->findOrFail($request->application_id)
            : \App\Models\LoanApplication::where('user_id', auth('borrower')->id())->latest()->first();
        if ($app) {
            $app->employment()->updateOrCreate(['application_id' => $app->id], $request->only(['employer_name','employer_type','job_title','department','employment_number','contact_number']));
        }
        return back()->with('success', 'Employment details updated.');
    }

    public function updateBank(Request $request) {
        $app = \App\Models\LoanApplication::where('user_id', auth('borrower')->id())->latest()->first();
        if ($app) {
            $app->bankDetails()->updateOrCreate(['application_id' => $app->id], $request->only(['bank_name','account_holder_name','account_number','account_type']));
        }
        return back()->with('success', 'Bank details updated.');
    }

    public function updateNextOfKin(Request $request) {
        $app = \App\Models\LoanApplication::where('user_id', auth('borrower')->id())->latest()->first();
        if ($app) {
            $app->nextOfKin()->updateOrCreate(['application_id' => $app->id, 'sort_order' => 1], [
                'first_name' => $request->nok_first_name, 'last_name' => $request->nok_last_name,
                'relationship' => $request->relationship, 'contact_number' => $request->nok_phone,
            ]);
        }
        return back()->with('success', 'Next of kin updated.');
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
