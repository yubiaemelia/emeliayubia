<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_code',
        'route_name',
    ];

    public function jeepneys(): HasMany
    {
        return $this->hasMany(Jeepney::class);
    }

    public function routeStages(): HasMany
    {
        return $this->hasMany(RouteStage::class);
    }
}