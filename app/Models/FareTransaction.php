<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FareTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_card_id',
        'jeepney_id',
        'boarding_stage_id',
        'alighting_stage_id',
        'distance_km',
        'calculated_fare',
        'balance_before',
        'balance_after',
        'tapped_in_at',
        'tapped_out_at',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'calculated_fare' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'tapped_in_at' => 'datetime',
            'tapped_out_at' => 'datetime',
        ];
    }

    public function passengerCard(): BelongsTo
    {
        return $this->belongsTo(PassengerCard::class);
    }

    public function jeepney(): BelongsTo
    {
        return $this->belongsTo(Jeepney::class);
    }

    public function boardingStage(): BelongsTo
    {
        return $this->belongsTo(
            RouteStage::class,
            'boarding_stage_id'
        );
    }

    public function alightingStage(): BelongsTo
    {
        return $this->belongsTo(
            RouteStage::class,
            'alighting_stage_id'
        );
    }
}