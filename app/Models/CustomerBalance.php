<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBalance extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany(CustomerBalanceDetail::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deposits()
    {
        return $this->hasMany(CustomerBalanceDeposit::class);
    }
}
