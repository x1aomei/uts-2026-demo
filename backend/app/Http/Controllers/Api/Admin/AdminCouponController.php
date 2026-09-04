<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    public function index()
    {
        return response()->json(Coupon::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'required|string|unique:coupons,code',
            'discount_type'  => 'required|in:fixed,percentage',
            'value'          => 'required|numeric|min:0',
            'min_spend'      => 'nullable|numeric|min:0',
            'expires_at'     => 'nullable|date',
        ]);

        $coupon = Coupon::create($validated);

        return response()->json(['message' => 'Kupon berhasil dibuat', 'data' => $coupon], 201);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['message' => 'Kupon berhasil dihapus']);
    }
}