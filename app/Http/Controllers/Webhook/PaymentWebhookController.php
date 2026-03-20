<?php
namespace App\Http\Controllers\Webhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
class PaymentWebhookController extends Controller {
    public function handle(Request $request) {
        \Illuminate\Support\Facades\Log::info('Payment webhook', $request->all());
        return response()->json(['status'=>'received']);
    }
}
