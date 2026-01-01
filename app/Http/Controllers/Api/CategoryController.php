<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['children' => function ($q) {
            $q->active()->ordered()->withCount('activeListings');
        }])
            ->parents()
            ->active()
            ->ordered()
            ->withCount('activeListings')
            ->get();

        return $this->successResponse($categories);
    }

    public function show($slug)
    {
        $category = Category::with(['children' => function ($q) {
            $q->active()->ordered()->withCount('activeListings');
        }, 'parent'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $breadcrumb = $category->getBreadcrumb();

        return $this->successResponse([
            'category' => $category,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function featured()
    {
        $categories = Category::featured()
            ->active()
            ->ordered()
            ->withCount('activeListings')
            ->limit(8)
            ->get();

        return $this->successResponse($categories);
    }

    public function tree()
    {
        $categories = Category::with('allChildren')
            ->parents()
            ->active()
            ->ordered()
            ->get();

        return $this->successResponse($categories);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $categories = Category::where('name', 'like', "%{$query}%")
            ->active()
            ->ordered()
            ->limit(10)
            ->get();

        return $this->successResponse($categories);
    }

    public function customFields($id)
    {
        $category = Category::findOrFail($id);

        return $this->successResponse([
            'fields' => $category->custom_fields ?? [],
        ]);
    }
}
