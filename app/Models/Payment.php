<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
class Payment extends Model
{
    use LogsActivity;
    protected $fillable = [
        'maintenance_fee_id',
        'amount',
        'paid_at',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function maintenanceFee(): BelongsTo
    {
        return $this->belongsTo(MaintenanceFee::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'maintenance_fee_id',
                'amount',
                'paid_at',
                'reference',
                'notes',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}