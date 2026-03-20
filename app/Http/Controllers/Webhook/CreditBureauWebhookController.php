<?php
namespace App\Http\Controllers\Webhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class CreditBureauWebhookController extends Controller {
    public function handle(Request $request) {
        \Illuminate\Support\Facades\Log::info('Credit bureau webhook', $request->all());
        return response()->json(['status'=>'received']);
    }
}
