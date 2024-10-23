<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SourceRepack extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function repack()
    {
        return $this->belongsTo(Repack::class);
    }

    public function inventoryDetail()
    {
        return $this->belongsTo(InventoryDetail::class);
    }
}
