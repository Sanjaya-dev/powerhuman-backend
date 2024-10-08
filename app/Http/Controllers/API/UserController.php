<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            // validation request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            // Find user by email
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error('Unauthorized', 401);
            }

            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid password');
            }
            // Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            // return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Login success');
        } catch (Exception $error) {
            return ResponseFormatter::error('Authentication failed');
        }
    }

    public function register(Request $request)
    {
        try {
            // Validation request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            // Create token
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            // Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Register success');
        } catch (Exception $error) {
            // Return error
            return ResponseFormatter::error($error->getMessage());
        }
    }

    public function logout(Request $request)
    {
        // revoke token
        $token = $request->user()->currentAccessToken()->delete();
        // return response
        return ResponseFormatter::success($token, 'Logout success');
    }

    public function fetch(Request $request)
    {
        // Get user
        $user = $request->user();
        // return response
        return ResponseFormatter::success($user, 'Fetch success');
    }
}
