<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page (Inertia).
     */
    public function view(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'is_active' => $user->is_active,
                'is_verified' => $user->is_verified,
                'email_verified_at' => $user->email_verified_at,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Display the profile settings edit page (Inertia).
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail(),
            'status' => session('status'),
        ]);
    }

    /**
     * Get the authenticated user's profile information.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'email_verified_at' => $user->email_verified_at,
                'two_factor_enabled' => $user->two_factor_secret !== null,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile information.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill($validated);

        // If email is being changed, reset verification
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Delete the authenticated user's account.
     */
    public function destroy(ProfileDeleteRequest $request): JsonResponse
    {
        $user = $request->user();

        // Verify email is verified before allowing deletion
        if (is_null($user->email_verified_at)) {
            return response()->json(
                ['message' => 'Email must be verified to delete account'],
                403
            );
        }

        // Delete user from database
        // Note: tokens are cascaded deleted via foreign key constraint
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
}
