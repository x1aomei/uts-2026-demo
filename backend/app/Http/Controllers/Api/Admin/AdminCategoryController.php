<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::with('children')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $validated['slug'] = Str::slug($request->name);
        $category = Category::create($validated);

        return response()->json(['message' => 'Kategori berhasil dibuat', 'data' => $category], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json(['message' => 'Kategori berhasil diperbarui', 'data' => $category]);
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}