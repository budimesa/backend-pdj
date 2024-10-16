<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    // price_status 0 = pending, 1 = confirmed
    // transaction_type 1 = incoming, 2 transfer, 3 repack

    use HasFactory;

    protected $guarded = [];

    public function incomingItem()
    {
        return $this->belongsTo(IncomingItem::class, 'incoming_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    public function inventoryDetails()
    {
        return $this->hasMany(InventoryDetail::class);
    }

    public function itemTransferDetails()
    {
        return $this->hasMany(ItemTransferDetail::class);
    }

    public function warehouse()
    {
        return $this->hasManyThrough(Warehouse::class, InventoryDetail::class, 'inventory_id', 'id', 'id', 'warehouse_id');
    }
    
}
