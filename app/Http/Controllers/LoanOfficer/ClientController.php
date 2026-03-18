<?php
namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\{User, LoanApplication};
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $officer = auth('officer')->user();
        $q = User::where('role', 'borrower')->where('assigned_officer_id', $officer->id)
            ->with(['loanApplications' => fn($q) => $q->latest()->take(1)]);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn($x) => $x->where('name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('national_id','like',"%$s%"));
        }
        return view('officer.clients.index', ['clients' => $q->latest()->paginate(20), 'filters' => $request->only(['search'])]);
    }

    public function search(Request $request)
    {
        $s = $request->input('q', '');
        $clients = User::where('role', 'borrower')
            ->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('phone','like',"%$s%")->orWhere('national_id','like',"%$s%"))
            ->take(10)->get(['id','name','phone','national_id']);
        return response()->json($clients);
    }

    public function show(User $client)
    {
        $client->load(['loanApplications.loanProduct', 'loans.loanProduct', 'loans.installments']);
        return view('officer.clients.show', compact('client'));
    }

    public function profile(User $client)      { return $this->show($client); }
    public function applications(User $client) { return $this->show($client); }
    public function loans(User $client)        { return $this->show($client); }
    public function documents(User $client)    { return $this->show($client); }
    public function payments(User $client)     { return $this->show($client); }
    public function notes(User $client)        { return $this->show($client); }

    public function addNote(Request $request, User $client)
    {
        $request->validate(['content' => 'required|string|max:1000']);
        $app = $client->loanApplications()->latest()->first();
        if ($app) {
            $app->notes()->create(['created_by' => auth('officer')->id(), 'type' => 'client', 'content' => $request->content, 'is_internal' => true]);
        }
        return back()->with('success', 'Note added.');
    }
}
