<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'stage_name',
        'stage_order',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function boardingTransactions(): HasMany
    {
        return $this->hasMany(
            FareTransaction::class,
            'boarding_stage_id'
        );
    }

    public function alightingTransactions(): HasMany
    {
        return $this->hasMany(
            FareTransaction::class,
            'alighting_stage_id'
        );
    }
}