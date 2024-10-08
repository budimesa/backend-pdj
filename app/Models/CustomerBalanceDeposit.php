<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBalanceDeposit extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function balance()
    {
        return $this->belongsTo(CustomerBalance::class);
    }
}
