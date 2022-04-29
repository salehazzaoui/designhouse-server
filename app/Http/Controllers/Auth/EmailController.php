<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1',)->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {
        // check if the url is a valid signed
        if (!URL::hasValidSignature($request)) {
            return response()->json(['error' => [
                'message' => 'Invalid verification link'
            ]], 422);
        }
        // check if user already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => [
                'message' => 'Email address already verified'
            ]], 422);
        }
        $user->markEmailAsVerified();
        event(new Verified($user));
        return response()->json(['message' => 'Email successfully verified'], 200);
    }

    public function resend(Request $request, User $user)
    {
        $this->validate($request, [
            'email' => ['email', 'required'],
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => [
                'email' => 'No user could be found with this email address'
            ]], 422);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['error' => [
                'message' => 'Email address already verified'
            ]], 422);
        }
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'verification link resent']);
    }
}
