<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Page;
use App\Models\Banner;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return $this->successResponse($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($validated['settings'] as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }

        return $this->successResponse(null, 'Settings updated');
    }

    // Pages management
    public function pages()
    {
        $pages = Page::ordered()->get();

        return $this->successResponse($pages);
    }

    public function createPage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'required|string|max:100|unique:pages',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $page = Page::create($validated);

        return $this->successResponse($page, 'Page created', 201);
    }

    public function updatePage(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:200',
            'slug' => 'sometimes|string|max:100|unique:pages,slug,' . $id,
            'content' => 'sometimes|string',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $page->update($validated);

        return $this->successResponse($page, 'Page updated');
    }

    public function deletePage($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return $this->successResponse(null, 'Page deleted');
    }

    // Banners management
    public function banners()
    {
        $banners = Banner::orderBy('position')->orderBy('order')->get();

        return $this->successResponse($banners);
    }

    public function createBanner(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'link' => 'nullable|url',
            'position' => 'required|in:home_top,home_middle,sidebar,listing_page,category_page',
            'order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $validated['image'] = $request->file('image')->store('banners', 'public');

        $banner = Banner::create($validated);

        return $this->successResponse($banner, 'Banner created', 201);
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:200',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'link' => 'nullable|url',
            'position' => 'sometimes|in:home_top,home_middle,sidebar,listing_page,category_page',
            'order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            \Storage::disk('public')->delete($banner->image);
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($validated);

        return $this->successResponse($banner, 'Banner updated');
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        \Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return $this->successResponse(null, 'Banner deleted');
    }
}
