<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemTransferDetail extends Model
{
    use HasFactory;
    
    protected $guarded = [];

   public function itemTransfer()
    {
        return $this->belongsTo(ItemTransfer::class);
    }

    // Mendefinisikan relasi Many-to-One dengan Inventory
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
