<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VendorLoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Authenticate a vendor and issue a long-lived API token.
     */
    public function login(VendorLoginRequest $request): JsonResponse
    {
        $throttleKey = 'vendor-login|'.Str::lower((string) $request->email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again in '
                    .RateLimiter::availableIn($throttleKey).' seconds.',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->isVendor()) {
            RateLimiter::hit($throttleKey, 60);

            return response()->json([
                'message' => 'Access denied. Vendor account required.',
            ], 403);
        }

        // Successful login clears the failed-attempt counter.
        RateLimiter::clear($throttleKey);

        // Issue a fresh token. We deliberately do NOT revoke the user's other
        // tokens: the mobile client sends a constant device_name, so revoking
        // by name would sign the vendor out on any other device they use.
        $token = $user->createToken($request->device_name, ['vendor'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Invalidate the current token (logout).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Return the authenticated vendor's profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'profile_photo' => $user->profile_photo,
        ]);
    }
}
