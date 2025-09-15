<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthenticatedUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): AuthenticatedUserResource
    {
        $credentials = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create($credentials);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return new AuthenticatedUserResource(Auth::user());
    }

    public function login(Request $request): AuthenticatedUserResource
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ( ! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['The provided credentials are incorrect.']);
        }

        $request->session()->regenerate();

        return new AuthenticatedUserResource(Auth::user());
    }

    public function user(): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(Auth::user());
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
