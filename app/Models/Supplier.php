<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function incomingItems()
    {
        return $this->hasMany(IncomingItem::class);
    }

    // Relasi ke Batch
    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}
