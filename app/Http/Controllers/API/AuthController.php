<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // Register a new user
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    // Login and issue a token
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->accessToken;

        return response()->json(['token' => $token], 200);
    }

    // Logout and revoke the token
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    // Redirect to the provider's OAuth2 page
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }


    public function handleProviderCallback($provider)
    {
        // Retrieve the user data from the provider
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create the user in your database
        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [

                'name' => $socialUser->getName(),
                'password' => Hash::make(uniqid()), // Random password
            ]
        );
        Auth::login($user, true);


        // Generate a Passport token
        $tokenResult = $user->createToken('auth_token'); // Returns PersonalAccessTokenResult
        $accessToken = $tokenResult->accessToken; // Extract the plain-text token

        // Log the token for debugging
        \Log::info('Generated Token:', ['token' => $accessToken]);

        // Redirect to the Vue app with the token
        return redirect()->away(
            'http://localhost:5173/auth/callback?token=' . urlencode($accessToken) . '&user=' . json_encode([
                'iduser' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
        );
    }


}
