<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repack extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function targetRepacks()
    {
        return $this->hasMany(TargetRepack::class);
    }

    public function sourceRepacks()
    {
        return $this->hasMany(SourceRepack::class);
    }
}
