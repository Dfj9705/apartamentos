<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reservation extends Model
{
    use LogsActivity;
    protected $fillable = [
        'user_id',
        'apartment_id',
        'common_area_id',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function commonArea(): BelongsTo
    {
        return $this->belongsTo(CommonArea::class);
    }

    public static function hasOverlap(
        int $commonAreaId,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreReservationId = null
    ): bool {
        return static::query()
            ->where('common_area_id', $commonAreaId)
            ->whereDate('reservation_date', $date)
            ->where('status', 'confirmed')
            ->when(
                $ignoreReservationId,
                fn($query) => $query->where('id', '!=', $ignoreReservationId)
            )
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    public function hasEnded(): bool
    {
        if ($this->status !== 'confirmed') {
            return false;
        }

        $end = Carbon::parse(
            $this->reservation_date->format('Y-m-d') . ' ' . $this->end_time
        );

        return $end->isPast();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id',
                'apartment_id',
                'common_area_id',
                'reservation_date',
                'start_time',
                'end_time',
                'status',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}