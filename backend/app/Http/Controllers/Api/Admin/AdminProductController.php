<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'base_price'  => 'required|numeric|min:0',
            'brand'        => 'nullable|string',
            'gender'       => 'required|in:men,women,unisex',
        ]);

        $validated['slug'] = Str::slug($request->name) . '-' . Str::random(5);
        $product = Product::create($validated);

        return response()->json(['message' => 'Produk berhasil ditambahkan', 'data' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'base_price'  => 'sometimes|numeric|min:0',
            'brand'       => 'nullable|string',
            'gender'      => 'sometimes|in:men,women,unisex',
            'is_active'   => 'sometimes|boolean',
        ]);

        $product->update($validated);

        return response()->json(['message' => 'Produk berhasil diperbarui', 'data' => $product]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }
}