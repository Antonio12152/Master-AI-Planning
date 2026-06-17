<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    /**
     * List all users (admin only)
     * Includes pagination, filtering by active/verified status
     */
    public function indexUsers(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user()->is_admin) {
            return response()->json(
                ['message' => 'Unauthorized. Admin access required.'],
                403
            );
        }

        $query = User::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        // Filter by verified status
        if ($request->has('is_verified')) {
            $query->where('is_verified', (bool) $request->input('is_verified'));
        }

        // Filter by admin status
        if ($request->has('is_admin')) {
            $query->where('is_admin', (bool) $request->input('is_admin'));
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort options
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        if (in_array($sortBy, ['name', 'email', 'created_at', 'last_login_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min((int) $request->input('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    /**
     * View individual user details (admin only)
     * Does NOT return user's plans
     */
    public function showUser(Request $request, User $user): JsonResponse
    {
        // Check if requester is admin
        if (!$request->user()->is_admin) {
            return response()->json(
                ['message' => 'Unauthorized. Admin access required.'],
                403
            );
        }

        // Return user without sensitive fields and without plans
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'is_active' => $user->is_active,
                'is_verified' => $user->is_verified,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'last_login_at' => $user->last_login_at,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    /**
     * Update user status (admin only)
     * Can activate/deactivate, verify/unverify, promote/demote to admin
     */
    public function updateUserStatus(Request $request, User $user): JsonResponse
    {
        // Check if requester is admin
        if (!$request->user()->is_admin) {
            return response()->json(
                ['message' => 'Unauthorized. Admin access required.'],
                403
            );
        }

        $validated = $request->validate([
            'is_active' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'is_verified' => $user->is_verified,
                'is_admin' => $user->is_admin,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }
}
