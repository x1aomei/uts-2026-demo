<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->addresses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'          => 'nullable|string',
            'recipient_name' => 'required|string',
            'phone'          => 'required|string',
            'address'        => 'required|string',
            'city'           => 'required|string',
            'province'       => 'required|string',
            'postal_code'    => 'required|string',
            'is_default'     => 'boolean',
        ]);

        $user = $request->user();

        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return response()->json(['message' => 'Alamat berhasil ditambahkan', 'address' => $address], 201);
    }
}