<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();
        return response()->json($categories);
    }

    public function show($slug)
    {
        $category = Category::with('products')->where('slug', $slug)->firstOrFail();
        return response()->json($category);
    }
}