<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransfer extends Model
{
    // transfer_status = 1 => sent, 2 => received, 3 => pending
    use HasFactory;

    protected $guarded = [];

    public function itemTransferDetails()
    {
        return $this->hasMany(ItemTransferDetail::class);
    }
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function inventories()
    {
        return $this->hasManyThrough(Inventory::class, ItemTransferDetail::class, 'item_transfer_id', 'id', 'id', 'inventory_id');
    }
}
