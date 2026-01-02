<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\Category;
use App\Models\ContactView;
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

        // Fuzzy Search with multiple strategies
        if ($request->filled('q')) {
            $search = trim($request->q);
            $searchTerms = $this->generateSearchTerms($search);
            $words = preg_split('/\s+/', $search);

            $query->where(function ($q) use ($search, $searchTerms, $words) {
                // Strategy 1: Exact match
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");

                // Strategy 2: Word-by-word matching (for partial words)
                foreach ($words as $word) {
                    if (strlen($word) >= 2) {
                        // Partial match - catches "mobil" in "mobile"
                        $q->orWhere('title', 'like', "%{$word}%");
                        $q->orWhere('description', 'like', "%{$word}%");
                    }
                }

                // Strategy 3: SOUNDEX phonetic matching per word in title
                foreach ($words as $word) {
                    if (strlen($word) >= 3) {
                        // Match any word in title that sounds similar
                        $q->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(title, ' ', 1)) = SOUNDEX(?)", [$word]);
                        $q->orWhereRaw("SOUNDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(title, ' ', 2), ' ', -1)) = SOUNDEX(?)", [$word]);
                    }
                }

                // Strategy 4: Generated typo variations
                foreach ($searchTerms as $term) {
                    if ($term !== strtolower($search) && strlen($term) >= 3) {
                        $q->orWhere('title', 'like', "%{$term}%");
                    }
                }
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

        // Location-based search with distance calculation
        $hasLocation = $request->filled('latitude') && $request->filled('longitude');
        if ($hasLocation) {
            $lat = $request->latitude;
            $lng = $request->longitude;

            // Add distance calculation using Haversine formula (returns km)
            $haversine = "(6371 * acos(LEAST(1.0, cos(radians($lat))
                         * cos(radians(latitude))
                         * cos(radians(longitude) - radians($lng))
                         + sin(radians($lat))
                         * sin(radians(latitude)))))";

            // Calculate distance for all listings, don't filter by radius
            $query->selectRaw("listings.*, {$haversine} AS distance");
        }

        // Sorting - when location is provided, always sort by nearest first, then by selected sort
        $sortBy = $request->input('sort', 'newest');

        // If location provided, always sort by nearest first
        if ($hasLocation) {
            $query->orderByRaw('CASE WHEN latitude IS NULL OR longitude IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('distance', 'asc');
        }

        switch ($sortBy) {
            case 'nearest':
                // Already handled above when hasLocation
                if (!$hasLocation) {
                    // Fallback to newest if no location
                    $query->orderBy('is_featured', 'desc')
                        ->orderBy('published_at', 'desc');
                }
                break;
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

        // Limit pagination to prevent DoS
        $perPage = min((int) $request->input('per_page', 20), 50);
        $listings = $query->paginate($perPage);

        return $this->paginatedResponse($listings);
    }

    public function show($slugOrId)
    {
        $listing = Listing::with([
            'user:id,name,phone,avatar,city,rating,total_reviews,created_at,is_verified_seller',
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
            'locality' => 'nullable|string|max:100',
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
                'locality' => $validated['locality'] ?? null,
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
            'locality' => 'nullable|string|max:100',
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

    public function trackContactView(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);

        // Don't track if viewing own listing
        if ($listing->user_id === auth()->id()) {
            return $this->successResponse(['tracked' => false], 'Own listing');
        }

        // Create contact view record
        ContactView::create([
            'viewer_id' => auth()->id(),
            'listing_id' => $listing->id,
            'owner_id' => $listing->user_id,
            'contact_type' => $request->input('type', 'phone'),
            'ip_address' => $request->ip(),
        ]);

        return $this->successResponse(['tracked' => true], 'Contact view tracked');
    }

    protected function storeImage(Listing $listing, $file, bool $isPrimary = false): ListingImage
    {
        // Increase memory limit temporarily for image processing
        $originalMemory = ini_get('memory_limit');
        ini_set('memory_limit', '256M');

        try {
            $uuid = Str::uuid();

            // Use WebP for better compression (30-50% smaller than JPEG)
            $mainFilename = $uuid . '.webp';
            $thumbFilename = 'thumb_' . $uuid . '.webp';

            $mainPath = "listings/{$listing->id}/{$mainFilename}";
            $thumbnailPath = "listings/{$listing->id}/{$thumbFilename}";

            // Create main image: max 1200px, WebP, 80% quality
            $mainImage = Image::make($file);

            // Resize to max 1200px maintaining aspect ratio
            $mainImage->resize(1200, 1200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Don't upscale smaller images
            });

            // Encode as WebP with 80% quality
            $mainEncoded = $mainImage->encode('webp', 80);
            Storage::disk('public')->put($mainPath, $mainEncoded);
            $compressedSize = strlen($mainEncoded);

            // Free memory before creating thumbnail
            $mainImage->destroy();

            // Create thumbnail: 300x300, WebP, 80% quality
            $thumbnail = Image::make($file)->fit(300, 300);
            $thumbEncoded = $thumbnail->encode('webp', 80);
            Storage::disk('public')->put($thumbnailPath, $thumbEncoded);
            $thumbnail->destroy();

            return ListingImage::create([
                'listing_id' => $listing->id,
                'path' => $mainPath,
                'thumbnail' => $thumbnailPath,
                'medium' => $mainPath, // Point to main image for backwards compatibility
                'original_name' => $file->getClientOriginalName(),
                'size' => $compressedSize, // Store compressed size
                'mime_type' => 'image/webp',
                'order' => $listing->images()->count(),
                'is_primary' => $isPrimary,
            ]);
        } finally {
            // Restore original memory limit
            ini_set('memory_limit', $originalMemory);
        }
    }

    /**
     * Generate fuzzy search terms from input query
     * Handles common typos and variations
     */
    protected function generateSearchTerms(string $query): array
    {
        $terms = [$query];
        $words = preg_split('/\s+/', strtolower($query));

        // Common character substitutions (keyboard adjacency)
        $substitutions = [
            'a' => ['s', 'q', 'z'],
            'e' => ['w', 'r', '3'],
            'i' => ['u', 'o', '8', '9'],
            'o' => ['i', 'p', '0'],
            'u' => ['y', 'i'],
            's' => ['a', 'd', 'z'],
            'n' => ['m', 'b'],
            'm' => ['n'],
            'c' => ['v', 'x', 'k'],
            'k' => ['c', 'l'],
            't' => ['r', 'y'],
            'ph' => ['f'],
            'f' => ['ph'],
        ];

        // Common Indian English variations
        $indianVariations = [
            'mobile' => ['phone', 'cell', 'handset'],
            'phone' => ['mobile', 'cell'],
            'car' => ['vehicle', 'auto'],
            'bike' => ['motorcycle', 'two wheeler', 'scooter'],
            'flat' => ['apartment', 'house'],
            'apartment' => ['flat', 'house'],
            'sofa' => ['couch', 'settee'],
            'ac' => ['air conditioner', 'air conditioning'],
            'tv' => ['television', 'led'],
            'fridge' => ['refrigerator', 'freezer'],
            'laptop' => ['notebook', 'computer'],
        ];

        foreach ($words as $word) {
            // Add variations for common words
            if (isset($indianVariations[$word])) {
                foreach ($indianVariations[$word] as $variation) {
                    $newTerms = str_replace($word, $variation, strtolower($query));
                    $terms[] = $newTerms;
                }
            }

            // Generate typo variations (single character substitutions)
            if (strlen($word) >= 4) {
                for ($i = 0; $i < strlen($word); $i++) {
                    $char = $word[$i];
                    if (isset($substitutions[$char])) {
                        foreach ($substitutions[$char] as $sub) {
                            $typoWord = substr_replace($word, $sub, $i, 1);
                            $terms[] = str_replace($word, $typoWord, strtolower($query));
                        }
                    }
                }
            }

            // Missing/doubled character variations
            if (strlen($word) >= 4) {
                // Missing character
                for ($i = 0; $i < strlen($word); $i++) {
                    $missingChar = substr($word, 0, $i) . substr($word, $i + 1);
                    if (strlen($missingChar) >= 3) {
                        $terms[] = str_replace($word, $missingChar, strtolower($query));
                    }
                }
            }
        }

        return array_unique(array_slice($terms, 0, 20)); // Limit to 20 variations
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function searchSuggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        if (strlen($query) < 2) {
            return $this->successResponse(['suggestions' => []]);
        }

        // Get matching titles
        $suggestions = Listing::active()
            ->where('title', 'like', "%{$query}%")
            ->select('title')
            ->distinct()
            ->limit(10)
            ->pluck('title')
            ->map(function ($title) use ($query) {
                // Highlight matching part
                return [
                    'text' => $title,
                    'highlight' => stripos($title, $query) !== false,
                ];
            });

        // Get matching categories
        $categories = Category::where('name', 'like', "%{$query}%")
            ->active()
            ->limit(5)
            ->get(['name', 'slug'])
            ->map(function ($cat) {
                return [
                    'text' => $cat->name,
                    'type' => 'category',
                    'slug' => $cat->slug,
                ];
            });

        return $this->successResponse([
            'suggestions' => $suggestions,
            'categories' => $categories,
        ]);
    }
}
