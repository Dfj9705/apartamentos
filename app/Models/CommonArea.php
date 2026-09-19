<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'opening_time',
        'closing_time',
        'reservation_duration',
        'available_days',
        'is_active',
    ];
    protected $casts = [
        'available_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function isAvailableAt(Carbon $dateTime): bool
    {
        // El área debe estar activa
        if (!$this->is_active) {
            return false;
        }

        // El día debe estar habilitado
        if (!in_array($dateTime->dayOfWeek, $this->available_days ?? [])) {
            return false;
        }

        $opening = Carbon::parse(
            $dateTime->format('Y-m-d') . ' ' . $this->opening_time
        );

        $closing = Carbon::parse(
            $dateTime->format('Y-m-d') . ' ' . $this->closing_time
        );

        // La reserva debe iniciar dentro del horario permitido
        if ($dateTime->lt($opening)) {
            return false;
        }

        // Calculamos cuándo terminaría la reserva
        $reservationEnd = $dateTime->copy()
            ->addMinutes($this->reservation_duration);

        // No permitimos que termine después del cierre
        if ($reservationEnd->gt($closing)) {
            return false;
        }

        return true;
    }

    public function isAvailableOnDate(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return in_array(
            $date->dayOfWeek,
            $this->available_days ?? []
        );
    }

    public function getAvailableTimeSlots(Carbon $date): array
    {
        if (!$this->isAvailableOnDate($date)) {
            return [];
        }

        $current = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $this->opening_time
        );

        $closing = Carbon::parse(
            $date->format('Y-m-d') . ' ' . $this->closing_time
        );

        $slots = [];

        while (
            $current->copy()
                ->addMinutes($this->reservation_duration)
                ->lte($closing)
        ) {
            $end = $current->copy()
                ->addMinutes($this->reservation_duration);

            $slots[] = [
                'start' => $current->format('H:i'),
                'end' => $end->format('H:i'),
            ];

            $current->addMinutes($this->reservation_duration);
        }

        return $slots;
    }
}