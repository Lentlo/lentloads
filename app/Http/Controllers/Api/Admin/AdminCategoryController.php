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
        $categories = Category::with(['children' => fn($q) => $q->withCount('listings')->orderBy('order')])
            ->parents()
            ->withCount('listings')
            ->orderBy('order')
            ->get();

        return $this->successResponse($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|mimes:svg,png|max:512',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'custom_fields' => 'nullable|array',
            'order' => 'integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('categories/icons', 'public');
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories/images', 'public');
        }

        $category = Category::create($validated);

        return $this->successResponse($category, 'Category created', 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'slug' => 'sometimes|string|max:100|unique:categories,slug,' . $id,
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|mimes:svg,png|max:512',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'custom_fields' => 'nullable|array',
            'order' => 'integer',
        ]);

        // Prevent setting parent as itself or its child
        if (isset($validated['parent_id'])) {
            if ($validated['parent_id'] == $id) {
                return $this->errorResponse('Category cannot be its own parent', 422);
            }
            $childIds = $category->getAllChildrenIds();
            if (in_array($validated['parent_id'], $childIds)) {
                return $this->errorResponse('Cannot set a child category as parent', 422);
            }
        }

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                \Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $request->file('icon')->store('categories/icons', 'public');
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories/images', 'public');
        }

        $category->update($validated);

        return $this->successResponse($category, 'Category updated');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        if ($category->listings()->count() > 0) {
            return $this->errorResponse('Cannot delete category with listings', 422);
        }

        if ($category->children()->count() > 0) {
            return $this->errorResponse('Cannot delete category with subcategories', 422);
        }

        // Delete images
        if ($category->icon) {
            \Storage::disk('public')->delete($category->icon);
        }
        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return $this->successResponse(null, 'Category deleted');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.order' => 'required|integer',
            'categories.*.parent_id' => 'nullable|exists:categories,id',
        ]);

        foreach ($validated['categories'] as $item) {
            Category::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'],
            ]);
        }

        return $this->successResponse(null, 'Categories reordered');
    }
}
