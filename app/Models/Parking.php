<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parking extends Model
{
    use HasFactory, SoftDeletes;

    public function slots(): HasMany {
        return $this->hasMany(Slot::class);
    }

    public function reserves(): HasManyThrough {
        return $this->hasManyThrough(Reserve::class, Slot::class);
    }

    protected $fillable = ['name','address', 'free_at_time', 'fee'];
}
