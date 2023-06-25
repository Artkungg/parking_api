<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Parking extends Model
{
    use HasFactory;

    public function slots(): HasMany {
        return $this->hasMany(Slot::class);
    }

    public function reserves(): HasManyThrough {
        return $this->hasManyThrough(Reserve::class, Slot::class);
    }
}
