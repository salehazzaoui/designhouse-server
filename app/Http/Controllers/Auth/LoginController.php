<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user || Hash::check($request->password, $user->password) === false) {
            return response()->json([
                'message' => 'invalide credential'
            ], 402);
        }
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'You must verify your email first',
                'status' => 401
            ], 401);
        }
        $token = $user->createToken($request->email)->plainTextToken;
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        $user = User::find(auth()->id());
        $user->tokens()->delete();

        return response()->json(['message' => 'logged out'], 200);
    }
}
