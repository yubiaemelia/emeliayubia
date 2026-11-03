<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PassengerCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'passenger_card_no',
        'card_type',
        'card_balance',
    ];

    protected function casts(): array
    {
        return [
            'card_balance' => 'decimal:2',
        ];
    }

    public function fareTransactions(): HasMany
    {
        return $this->hasMany(FareTransaction::class);
    }
}