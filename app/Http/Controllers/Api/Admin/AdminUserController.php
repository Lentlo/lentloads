<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount(['listings', 'reviewsReceived']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Sorting
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->input('per_page', 20));

        return $this->paginatedResponse($users);
    }

    public function show($id)
    {
        $user = User::withCount(['listings', 'reviewsReceived', 'favorites'])
            ->with(['listings' => fn($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        return $this->successResponse($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|in:user,admin,moderator',
            'status' => 'sometimes|in:active,suspended,banned',
            'is_verified_seller' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return $this->successResponse($user, 'User updated successfully');
    }

    public function suspend(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return $this->errorResponse('Cannot suspend an admin user', 422);
        }

        $user->update(['status' => 'suspended']);

        // Optionally deactivate all listings
        $user->listings()->active()->update(['status' => 'pending']);

        return $this->successResponse($user, 'User suspended');
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        return $this->successResponse($user, 'User activated');
    }

    public function verifySeller($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified_seller' => true]);

        return $this->successResponse($user, 'Seller verified');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return $this->errorResponse('Cannot delete an admin user', 422);
        }

        // Soft delete
        $user->delete();

        return $this->successResponse(null, 'User deleted');
    }
}
