<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingItem extends Model
{
    use HasFactory;

    // price_status 0 = pending, 1 = confirmed
    // transaction_type = 0 non-repack, 1 = repack

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by'); // Menentukan foreign key
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
