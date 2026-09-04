<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createSnapToken(Request $request, $orderId)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($orderId);

        $snapToken = "SNAP-TOKEN-" . time();

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_method' => 'midtrans',
                'amount'         => $order->total_amount,
                'status'         => 'pending',
                'snap_token'     => $snapToken
            ]
        );

        return response()->json(['snap_token' => $snapToken, 'payment' => $payment]);
    }

    public function notification(Request $request)
    {
        return response()->json(['message' => 'Webhook received']);
    }
}