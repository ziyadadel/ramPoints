<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    public function generateVerificationCode()
    {
        // Generate a random 6-digit verification code
        $verificationCode = random_int(100000, 999999);
        return $verificationCode;
    }

    public function sendVerificationCode(Request $request)
    {
        // Assuming you have a 'email' field in your user table
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $verificationCode = $this->generateVerificationCode();

        // Store the hashed verification code in the user record
        $user->verification_code = Hash::make($verificationCode);
        $user->save();

        // Send the verification code to the user via SMS or any other method
        // Implement your SMS sending logic here
        
        return response()->json(['message' => 'Verification code sent successfully'], 200);
    }

    public function verify(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if the provided verification code matches the stored value
        if (Hash::check($request->input('verification_code'), $user->verification_code)) {
            // Verification successful
            $user->verification_code = null; // Clear the verification code after successful verification
            $user->save();
            return response()->json(['message' => 'Verification successful'], 200);
        } else {
            // Verification failed
            return response()->json(['message' => 'Invalid verification code'], 400);
        }
    }
}
