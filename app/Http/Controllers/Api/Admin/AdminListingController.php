<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class AdminListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::with(['user:id,name,email', 'category:id,name', 'primaryImage']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Sorting - validate against allowed columns to prevent SQL injection
        $allowedSortColumns = ['created_at', 'updated_at', 'title', 'price', 'views_count', 'status'];
        $sortBy = in_array($request->input('sort'), $allowedSortColumns)
            ? $request->input('sort')
            : 'created_at';
        $sortOrder = in_array(strtolower($request->input('order')), ['asc', 'desc'])
            ? $request->input('order')
            : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Limit pagination to prevent DoS
        $perPage = min((int) $request->input('per_page', 20), 100);
        $listings = $query->paginate($perPage);

        return $this->paginatedResponse($listings);
    }

    public function pending()
    {
        $listings = Listing::with(['user:id,name,email', 'category:id,name', 'images'])
            ->pending()
            ->oldest()
            ->paginate(20);

        return $this->paginatedResponse($listings);
    }

    public function show($id)
    {
        $listing = Listing::with([
            'user:id,name,email,phone,city',
            'category:id,name,parent_id',
            'category.parent:id,name',
            'images',
            'reports' => fn($q) => $q->pending(),
        ])->findOrFail($id);

        return $this->successResponse($listing);
    }

    public function approve($id)
    {
        $listing = Listing::findOrFail($id);

        $listing->publish();

        // Notify user
        // $listing->user->notify(new ListingApproved($listing));

        return $this->successResponse($listing, 'Listing approved');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $listing = Listing::findOrFail($id);

        $listing->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
        ]);

        // Notify user
        // $listing->user->notify(new ListingRejected($listing, $validated['reason']));

        return $this->successResponse($listing, 'Listing rejected');
    }

    public function feature(Request $request, $id)
    {
        $validated = $request->validate([
            'featured_until' => 'required|date|after:today',
            'is_urgent' => 'boolean',
            'is_highlighted' => 'boolean',
        ]);

        $listing = Listing::findOrFail($id);

        $listing->update([
            'is_featured' => true,
            'featured_until' => $validated['featured_until'],
            'is_urgent' => $validated['is_urgent'] ?? false,
            'is_highlighted' => $validated['is_highlighted'] ?? false,
        ]);

        return $this->successResponse($listing, 'Listing featured');
    }

    public function destroy($id)
    {
        $listing = Listing::findOrFail($id);

        // Delete images
        foreach ($listing->images as $image) {
            $image->deleteFiles();
        }

        $listing->forceDelete();

        return $this->successResponse(null, 'Listing permanently deleted');
    }
}
