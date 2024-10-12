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
        return $this->belongsTo(IncomingItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function itemTransfer()
    {
        return $this->belongsTo(ItemTransfer::class);
    }
}
