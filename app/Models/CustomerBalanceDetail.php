<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBalanceDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customerBalance()
    {
        return $this->belongsTo(CustomerBalance::class);
    }
}
