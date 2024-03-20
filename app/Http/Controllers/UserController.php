<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Hash;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Hash the password
            $passwordHash = Hash::make($request->password);

            // Create a new user instance
            $user = User::create([
                'email' => $request->email,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'role' => $request->role,
                'password' => $passwordHash,
            ]);

            // Return a response indicating success
            return response()->json(['user' => $user], 201);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to register user', 'error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('user_api')->attempt($credentials)) {
            $user = Auth::guard('user_api')->user();
            $token = auth('user_api')->login($user);

            return response()->json(['token' => $token, 'user' => $user]);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('user_api')->logout();

        return response()->json(['message' => 'Successfully logged out']);

    }

    public function refresh()
    {
        // Implement token refresh logic
    }

    public function allUsers()
    {
        $users = User::latest('updated_at')->get();

        return response()->json(['user' => $users]);
    }

    public function searchByDate(Request $request)
    {
        try {
            // Validate incoming request data
            $validator = Validator::make($request->all(), [
                'date' => 'required|date', // Validate record_date
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Convert record_date to Carbon instance
            $recordDate = Carbon::parse($request->date);

            // Query transactions where record_date is on or after the provided record_date
            $users = User::where('updated_at', '>=', $recordDate->toDateTimeString())->get();

            return response()->json(['users' => $users], 200);
        } catch (\Exception $e) {
            // Return a response indicating failure
            return response()->json(['message' => 'Failed to search transactions by record date', 'error' => $e->getMessage()], 500);
        }
    }
}
