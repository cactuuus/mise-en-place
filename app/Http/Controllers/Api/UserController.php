<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        return response()->json([
            'user'            => $user->only(['id', 'name', 'email', 'created_at']),
            'followers_count' => $user->followersCount(),
            'following_count' => $user->followingCount(),
            'recipes_count'   => $user->publicRecipes()->count(),
            'is_following'    => auth()->check() ? auth()->user()->isFollowing($user) : false,
        ]);
    }

    public function recipes(User $user)
    {
        $recipes = $user
            ->publicRecipes()
            ->with(['tags'])
            ->latest()
            ->paginate(20);

        return response()->json($recipes);
    }

    public function followers(User $user)
    {
        $followers = $user
            ->followers()
            ->select(['users.id', 'users.name', 'users.email'])
            ->paginate(20);

        return response()->json($followers);
    }

    public function following(User $user)
    {
        $following = $user
            ->following()
            ->select(['users.id', 'users.name', 'users.email'])
            ->paginate(20);

        return response()->json($following);
    }

    public function follow(User $user)
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

    public function unfollow(User $user)
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
}
