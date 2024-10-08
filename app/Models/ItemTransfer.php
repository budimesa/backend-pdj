<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
