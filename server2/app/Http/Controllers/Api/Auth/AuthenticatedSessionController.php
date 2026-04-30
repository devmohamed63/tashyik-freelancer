<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        $request->authenticate();

        $user = $request->user();

        // Logout from other devices
        if ($user->type == User::SERVICE_PROVIDER_ACCOUNT_TYPE) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('api');

        return response([
            'token' => $token->plainTextToken,
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        $user = $request->user();

        // Clear FCM token
        $user->update(['fcm_token' => null]);

        // Clear auth tokens
        $user->tokens()->delete();

        // Loguout
        Auth::guard('web')->logout();

        return response()->noContent();
    }
}
