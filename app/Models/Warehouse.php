<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventoryDetails()
    {
        return $this->hasMany(InventoryDetail::class);
    }

    public function itemTransfersFrom()
    {
        return $this->hasMany(ItemTransfer::class, 'from_warehouse_id');
    }

    public function itemTransfersTo()
    {
        return $this->hasMany(ItemTransfer::class, 'to_warehouse_id');
    }
}
