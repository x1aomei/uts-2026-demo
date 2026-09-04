<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $cart->load('items.productVariant.product.images');

        return response()->json($cart);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity'           => 'required|integer|min:1',
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        $item = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_variant_id' => $request->product_variant_id],
            ['quantity' => DB::raw("quantity + {$request->quantity}")]
        );

        return response()->json(['message' => 'Item berhasil ditambahkan ke keranjang', 'item' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        CartItem::where('cart_id', $cart->id)->where('id', $id)->delete();

        return response()->json(['message' => 'Item berhasil dihapus dari keranjang']);
    }
}