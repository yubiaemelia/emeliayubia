<?php

namespace App\Services;

use App\Models\Jeepney;
use Illuminate\Support\Facades\DB;

class JeepneyService
{
    // Create a new jeepney
    public function createJeepney(array $data): Jeepney
    {
        return DB::transaction(function () use ($data) {

            // Convert plate number to uppercase
            $data['jeepney_plate'] = strtoupper(
                trim($data['jeepney_plate'])
            );

            // Save jeepney
            return Jeepney::create($data);
        });
    }

    // Update an existing jeepney
    public function updateJeepney(int $id, array $data): Jeepney
    {
        return DB::transaction(function () use ($id, $data) {

            // Find jeepney
            $jeepney = Jeepney::findOrFail($id);

            // Convert plate number to uppercase
            $data['jeepney_plate'] = strtoupper(
                trim($data['jeepney_plate'])
            );

            // Update jeepney
            $jeepney->update($data);

            return $jeepney;
        });
    }
}
