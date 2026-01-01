<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['user:id,name,avatar,city,rating', 'category:id,name,slug', 'primaryImage'])
            ->active()
            ->notExpired();

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->inCategory($category->id);
            }
        }

        // Location filters
        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        // Price filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Condition filter
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        // Nearby search
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $radius = $request->input('radius', 50);
            $query->nearby($request->latitude, $request->longitude, $radius);
        }

        // Sorting
        $sortBy = $request->input('sort', 'newest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('published_at', 'desc');
        }

        $listings = $query->paginate($request->input('per_page', 20));

        return $this->paginatedResponse($listings);
    }

    public function show($slugOrId)
    {
        $listing = Listing::with([
            'user:id,name,avatar,city,rating,total_reviews,created_at,is_verified_seller',
            'category:id,name,slug,parent_id',
            'category.parent:id,name,slug',
            'images',
        ])
            ->where(function($q) use ($slugOrId) {
                $q->where('slug', $slugOrId);
                // Only check ID if the value is purely numeric
                if (is_numeric($slugOrId)) {
                    $q->orWhere('id', $slugOrId);
                }
            })
            ->firstOrFail();

        // Increment views
        $listing->incrementViews();

        // Check if favorited by current user
        $listing->is_favorited = $listing->isFavoritedBy(auth()->user());

        // Get similar listings
        $similar = Listing::with(['primaryImage'])
            ->active()
            ->where('id', '!=', $listing->id)
            ->where('category_id', $listing->category_id)
            ->inCity($listing->city)
            ->limit(6)
            ->get();

        return $this->successResponse([
            'listing' => $listing,
            'similar' => $similar,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'price_type' => 'required|in:fixed,negotiable,free,contact',
            'condition' => 'nullable|in:new,like_new,good,fair,poor',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'attributes' => 'nullable|array',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        DB::beginTransaction();

        try {
            $listing = Listing::create([
                'user_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'price_type' => $validated['price_type'],
                'condition' => $validated['condition'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'model' => $validated['model'] ?? null,
                'year' => $validated['year'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'] ?? null,
                'address' => $validated['address'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'attributes' => $validated['attributes'] ?? null,
                'status' => 'pending',
            ]);

            // Handle images
            foreach ($request->file('images') as $index => $image) {
                $this->storeImage($listing, $image, $index === 0);
            }

            // Update user's listing count
            auth()->user()->increment('total_listings');

            // Update category listing count
            $listing->category->updateListingsCount();

            DB::commit();

            return $this->successResponse(
                $listing->load('images'),
                'Listing created successfully. It will be visible after approval.',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create listing: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user()) && !auth()->user()->isModerator()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:100',
            'description' => 'sometimes|string|max:5000',
            'price' => 'sometimes|numeric|min:0',
            'price_type' => 'sometimes|in:fixed,negotiable,free,contact',
            'condition' => 'nullable|in:new,like_new,good,fair,poor',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'city' => 'sometimes|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'attributes' => 'nullable|array',
        ]);

        $listing->update($validated);

        return $this->successResponse($listing->load('images'), 'Listing updated successfully');
    }

    public function destroy($id)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user()) && !auth()->user()->isModerator()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Delete images
        foreach ($listing->images as $image) {
            $image->deleteFiles();
        }

        $listing->delete();

        return $this->successResponse(null, 'Listing deleted successfully');
    }

    public function markAsSold($id)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $listing->markAsSold();
        auth()->user()->increment('successful_sales');

        return $this->successResponse($listing, 'Listing marked as sold');
    }

    public function renew($id)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $listing->update([
            'expires_at' => now()->addDays(30),
            'status' => 'active',
        ]);

        return $this->successResponse($listing, 'Listing renewed for 30 days');
    }

    public function myListings(Request $request)
    {
        $listings = Listing::with(['primaryImage', 'category:id,name'])
            ->where('user_id', auth()->id())
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($listings);
    }

    public function userListings($userId)
    {
        $listings = Listing::with(['primaryImage', 'category:id,name'])
            ->where('user_id', $userId)
            ->active()
            ->latest()
            ->paginate(20);

        return $this->paginatedResponse($listings);
    }

    public function addImages(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $currentCount = $listing->images()->count();
        if ($currentCount + count($request->file('images')) > 10) {
            return $this->errorResponse('Maximum 10 images allowed', 422);
        }

        foreach ($request->file('images') as $image) {
            $this->storeImage($listing, $image, $currentCount === 0);
            $currentCount++;
        }

        return $this->successResponse($listing->load('images'), 'Images added successfully');
    }

    public function deleteImage($id, $imageId)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $image = ListingImage::where('listing_id', $id)->findOrFail($imageId);
        $wasPrimary = $image->is_primary;
        $image->delete();

        // Set new primary if needed
        if ($wasPrimary) {
            $newPrimary = $listing->images()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return $this->successResponse(null, 'Image deleted successfully');
    }

    public function setPrimaryImage($id, $imageId)
    {
        $listing = Listing::findOrFail($id);

        if (!$listing->isOwner(auth()->user())) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $listing->images()->update(['is_primary' => false]);
        ListingImage::where('listing_id', $id)->where('id', $imageId)->update(['is_primary' => true]);

        return $this->successResponse(null, 'Primary image updated');
    }

    protected function storeImage(Listing $listing, $file, bool $isPrimary = false): ListingImage
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "listings/{$listing->id}/{$filename}";

        // Store original
        Storage::disk('public')->put($path, file_get_contents($file));

        // Create thumbnail
        $thumbnailPath = "listings/{$listing->id}/thumb_{$filename}";
        $thumbnail = Image::make($file)->fit(300, 300);
        Storage::disk('public')->put($thumbnailPath, $thumbnail->encode());

        // Create medium size
        $mediumPath = "listings/{$listing->id}/medium_{$filename}";
        $medium = Image::make($file)->fit(800, 600);
        Storage::disk('public')->put($mediumPath, $medium->encode());

        return ListingImage::create([
            'listing_id' => $listing->id,
            'path' => $path,
            'thumbnail' => $thumbnailPath,
            'medium' => $mediumPath,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'order' => $listing->images()->count(),
            'is_primary' => $isPrimary,
        ]);
    }
}
