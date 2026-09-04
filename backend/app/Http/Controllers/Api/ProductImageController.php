<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate(['image' => 'required|image|max:2048']);
        $product = Product::findOrFail($productId);

        $path = $request->file('image')->store('products', 'public');
        $image = $product->images()->create([
            'image_url'  => asset('storage/' . $path),
            'is_primary' => $request->is_primary ?? false,
        ]);

        return response()->json(['message' => 'Gambar berhasil diunggah', 'data' => $image], 201);
    }

    public function destroy($id)
    {
        $image = ProductImage::findOrFail($id);
        $relativePath = str_replace(asset('storage/'), '', $image->image_url);
        Storage::disk('public')->delete($relativePath);
        $image->delete();

        return response()->json(['message' => 'Gambar berhasil dihapus']);
    }
}