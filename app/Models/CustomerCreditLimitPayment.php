<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCreditLimitPayment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function customerCreditLimit()
    {
        return $this->belongsTo(CustomerCreditLimit::class);
    }
}
