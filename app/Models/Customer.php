<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function creditLimits()
    {
        return $this->hasMany(CustomerCreditLimit::class);
    }
    
     public function balances()
    {
        return $this->hasMany(CustomerBalance::class);
    }
}
