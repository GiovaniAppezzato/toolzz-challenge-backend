<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Resources\AuthenticateResource;
use App\Http\Resources\UserResource;
use App\Models\User;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        // Check if the request has the required fields
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();

            return new AuthenticateResource([
                'access_token' => $user->createToken('auth')->accessToken,
                'user' => $user
            ], 201);
        }

        return response()->json('Unauthorized', 401);
    }

    public function signOut(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->noContent();
    }

    public function signUp(StoreUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create($request->validated());

            return new AuthenticateResource([
                'access_token' => $user->createToken('authToken')->accessToken,
                'user' => $user
            ], 201);
        });
    }

    public function show(Request $request)
    {
        return new UserResource($request->user()->load('photo'));
    }
}
