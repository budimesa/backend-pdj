<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function targetRepacks()
    {
        return $this->hasMany(TargetRepack::class);
    }

    public function sourceRepacks()
    {
        return $this->hasMany(SourceRepack::class);
    }
}
