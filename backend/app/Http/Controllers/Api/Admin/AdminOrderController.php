<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.productVariant.product', 'payment', 'shipment'])
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status pesanan berhasil diperbarui', 'data' => $order]);
    }
}