<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_voutcher extends Model
{
    // use HasFactory;

    protected $table = 'user_voutcher';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'num_of_point','user_id', 'voutcher_plan_id','branch_id', 'value_in_pounds', 'expiration_date','status','sold_date','status','voutcher_plan_name','voucher_number'
    ];

    /**
     * Get the user that owns the voucher.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the voucher plan associated with the voucher.
     */
    public function voutcher_plan()
    {
        return $this->belongsTo(Voutcher_plan::class);
    }
}
