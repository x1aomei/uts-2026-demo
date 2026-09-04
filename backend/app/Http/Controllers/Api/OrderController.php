<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['orderItems.productVariant.product', 'payment'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $user = $request->user();
        $cart = Cart::with('items.productVariant')->where('user_id', $user->id)->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang belanja kosong'], 400);
        }

        return DB::transaction(function () use ($user, $cart, $request) {
            $totalAmount = $cart->items->sum(function ($item) {
                return $item->productVariant->price * $item->quantity;
            });

            $order = Order::create([
                'user_id'      => $user->id,
                'address_id'   => $request->address_id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status'       => 'pending',
                'total_amount' => $totalAmount,
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $item->quantity,
                    'price_at_purchase'  => $item->productVariant->price,
                ]);
            }

            $cart->items()->delete();

            return response()->json(['message' => 'Pesanan berhasil dibuat', 'order' => $order], 201);
        });
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['orderItems.productVariant.product', 'payment', 'address', 'shipment'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }
}