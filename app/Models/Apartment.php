<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Apartment extends Model
{
    use LogsActivity;
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

    public function updateOccupancyStatus(): void
    {
        if ($this->status === 'maintenance') {
            return;
        }

        $this->update([
            'status' => $this->residents()
                ->where('is_active', true)
                ->exists()
                ? 'occupied'
                : 'available',
        ]);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->tower
            ? "Torre {$this->tower} - {$this->number}"
            : $this->number;
    }

    public function maintenanceFees(): HasMany
    {
        return $this->hasMany(MaintenanceFee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'number',
                'tower',
                'floor',
                'status',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}