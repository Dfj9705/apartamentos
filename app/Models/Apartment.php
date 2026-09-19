<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    protected $fillable = [
        'number',
        'tower',
        'floor',
        'status',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}