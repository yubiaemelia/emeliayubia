<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jeepney extends Model
{
    use HasFactory;

    protected $fillable = [
        'jeepney_plate',
        'route_id',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function fareTransactions(): HasMany
    {
        return $this->hasMany(FareTransaction::class);
    }
}