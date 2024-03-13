<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voutcher_plan extends Model
{
    // use HasFactory;

    protected $table = 'voutcher_plan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at', 'updated_at','id','name', 'number_of_points', 'order_number', 'value_in_pounds', 'number_of_days_to_expire','image','status'
    ];
}
