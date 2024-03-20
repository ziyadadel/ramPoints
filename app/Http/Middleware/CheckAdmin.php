<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class CheckAdmin
{
    public function handle($request, Closure $next)
    {
        $user = JWTAuth::parseToken()->authenticate();
        // return response()->json(['user' => $user]);
        // Get the user ID from the request (assuming it's passed as a query parameter)
        $email = $user->email;
        $role = $user->role;

        // Check if the user exists in the users table
        if ($role == 'user') {
            return response()->json(['error' => 'Admin not found'], 404);
        }
        

        return $next($request);
    }
}