<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //use HasFactory;

    protected $table = "transactions";

   /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'transaction_qr_code',
        'transaction_date',
        'transaction_number',
        'branch_id',
        'number_of_points',
        'record_date',
        'customer_id',
        'image',
        'created_at',
        'updated_at',
    ];
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
