<?php

namespace App\Users\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Persistence\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Users\Events\UserLoggedIn;
use App\Users\Events\UserLoggedOut;
use App\Users\Events\UserRegistered;

class AuthController
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            event(new UserLoggedIn($user, $request->ip()));

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        }

        return response()->json(['message' => 'Invalid login details'], 401);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'RECRUITER'
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        event(new UserRegistered($user, $request->ip()));

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        event(new UserLoggedOut($user, $request->ip()));

        return response()->json(['message' => 'Successfully logged out']);
    }
}
