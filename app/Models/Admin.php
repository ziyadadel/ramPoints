<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable; // Import the Authenticatable class


class Admin extends Authenticatable implements JWTSubject
{
    // use HasFactory;

    protected $table = "admins";

    protected $fillable = [
        'username', 'email','password'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Check if the user is an admin
    public function isAdmin()
    {
        return $this->role === 'admin'; // Adjust this according to your admin role checking logic
    }
}
