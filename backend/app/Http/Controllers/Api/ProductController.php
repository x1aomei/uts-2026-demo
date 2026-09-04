<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'images', 'variants'])
            ->where('is_active', true)
            ->latest()
            ->paginate(12);

        return response()->json($products);
    }

    public function show($slug)
    {
        $product = Product::with(['category', 'images', 'variants', 'reviews.user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($product);
    }
}