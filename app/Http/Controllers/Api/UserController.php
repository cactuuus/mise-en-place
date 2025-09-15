<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthenticatedUserResource;
use App\Http\Resources\RecipePreviewCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user'            => $user->only(['id', 'name', 'avatar_urls']),
            'followers_count' => $user->followersCount(),
            'following_count' => $user->followingCount(),
            'recipes_count'   => $user->publicRecipes()->count(),
            'is_following'    => auth()->check() ? auth()->user()->isFollowing($user) : false,
        ]);
    }

    public function recipes(User $user): RecipePreviewCollection
    {
        $recipes = $user
            ->publicRecipes()
            ->with(['tags:name,type', 'media'])
            ->latest()
            ->paginate(20);

        return new RecipePreviewCollection($recipes);
    }

    public function followers(User $user): JsonResponse
    {
        $followers = $user
            ->followers()
            ->select(['users.id', 'users.name'])
            ->paginate(20);

        return response()->json($followers);
    }

    public function following(User $user): JsonResponse
    {
        $following = $user
            ->following()
            ->select(['users.id', 'users.name'])
            ->paginate(20);

        return response()->json($following);
    }

    public function follow(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 400);
        }

        auth()->user()->follow($user);

        return response()->json([
            'message'      => 'User followed successfully',
            'is_following' => true,
        ]);
    }

    public function unfollow(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Cannot unfollow yourself'], 400);
        }

        auth()->user()->unfollow($user);

        return response()->json([
            'message'      => 'User unfollowed successfully',
            'is_following' => false,
        ]);
    }

    public function uploadAvatar(Request $request): AuthenticatedUserResource
    {
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
        ]);

        $user->clearMediaCollection('avatar');
        $user
            ->addMediaFromRequest('avatar')
            ->usingName('avatar')
            ->toMediaCollection('avatar');

        return new AuthenticatedUserResource($user);
    }

    public function deleteAvatar(): AuthenticatedUserResource
    {
        $user = Auth::user();
        $user->clearMediaCollection('avatar');

        return new AuthenticatedUserResource($user);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = Auth::user();

        $password = $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if ( ! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'errors'  => [
                    'current_password' => ['Current password is incorrect'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = auth()->user();
        $user->clearMediaCollection('avatar');
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    public function updateName(Request $request): AuthenticatedUserResource
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update(['name' => $request->name]);

        return new AuthenticatedUserResource($user);
    }
}
