<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Resident extends Model
{
    use LogsActivity;
    protected $fillable = [
        'apartment_id',
        'user_id',
        'name',
        'phone',
        'email',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    protected static function booted(): void
    {
        static::saved(function (Resident $resident) {
            $resident->apartment?->updateOccupancyStatus();

            if ($resident->wasChanged('apartment_id')) {
                $oldApartmentId = $resident->getOriginal('apartment_id');

                Apartment::find($oldApartmentId)
                        ?->updateOccupancyStatus();
            }

            if ($resident->user) {
                if (!$resident->user->hasRole('Residente')) {
                    $resident->user->assignRole('Residente');
                }
            }
        });

        static::deleted(function (Resident $resident) {
            $resident->apartment?->updateOccupancyStatus();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'apartment_id',
                'user_id',
                'name',
                'phone',
                'email',
                'type',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}