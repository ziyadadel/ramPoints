<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_details extends Model
{
    // use HasFactory;

    protected $table = "company_details";

    protected $fillable = ['created_at', 'updated_at','id','company_name', 'logo'];
}
