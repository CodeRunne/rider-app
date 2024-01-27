<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\LoginNeedsVerification;

class LoginController extends Controller
{
    public function submit(Request $request) {
        
        // Validate phone number
        $request->validate([
            'phone' => ['required', 'numeric', 'min:10'],
        ]);

        // Find or create user
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if (!$user) {
            return response()->json([
                'message' => 'Could not process a user with that phone number'
            ], 401);
        }

        // Notify user
        $user->notify(new LoginNeedsVerification());

        // return back a response
        return response()->json([
            'message' => 'Text message notification sent.'
        ]);
    }

    public function verify(Request $request) {
        
        // Validate the incoming request
        $request->validate([
            'phone' => ['required', 'numeric', 'min:10'],
            'login_code' => ['required', 'numeric', 'between:111111,999999'],
        ]);

        // find user
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)
            ->first();

        // is the code provided the same one saved?
        // if so return back an auth token
        if ($user) {
            // Change Login Code To Nullable
            $user->update(['login_code' => null]);

            // Return token to user
            return $user->createToken($request->login_code)->plainTextToken;
        }

        return response()->json([
            'message' => 'Invalid verification code.'
        ], 401);
    }
}
