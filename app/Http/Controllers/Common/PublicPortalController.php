<?php
namespace App\Http\Controllers\Common;
use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use Illuminate\Http\Request;

class PublicPortalController extends Controller
{
    public function index() {
        return view('borrower.welcome', ['products' => LoanProduct::active()->get()]);
    }

    public function about()    { return view('borrower.public.about'); }
    public function products() { return view('borrower.public.products', ['products' => LoanProduct::active()->get()]); }
    public function faq()      { return view('borrower.public.faq'); }
    public function contact()  { return view('borrower.public.contact'); }
    public function privacy()  { return view('borrower.public.privacy'); }
    public function terms()    { return view('borrower.public.terms'); }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'message' => 'required|string|max:2000',
        ]);
        // TODO: Mail::to('info@myloan.co.ls')->send(new ContactFormMail($request->all()));
        return back()->with('success', 'Thank you, ' . $request->name . '. We received your message and will respond within one business day.');
    }
}
