<?php

namespace App\Services;

use App\Models\FareTransaction;
use App\Models\Jeepney;
use App\Models\PassengerCard;
use App\Models\RouteStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FareTransactionService
{
    // Assumed rate per kilometer
    private const RATE_PER_KM = 10.00;

    // Calculate fare
    private function calculateFare(
        float $distance,
        string $cardType
    ): float {

        $fare = $distance * self::RATE_PER_KM;

        // Apply 20% discount for Student and Senior
        if (in_array($cardType, ['Student', 'Senior'], true)) {
            $fare = $fare * 0.80;
        }

        return round($fare, 2);
    }

    // Validate route stages
    private function validateTrip(
        Jeepney $jeepney,
        RouteStage $boarding,
        RouteStage $alighting
    ): void {

        if (
            $boarding->route_id != $jeepney->route_id ||
            $alighting->route_id != $jeepney->route_id
        ) {
            throw ValidationException::withMessages([
                'boarding_stage_id' => 'Both stages must belong to the jeepney route.',
            ]);
        }

        if ($alighting->stage_order <= $boarding->stage_order) {
            throw ValidationException::withMessages([
                'alighting_stage_id' => 'Alighting stage must come after boarding stage.',
            ]);
        }
    }

    // CREATE FARE TRANSACTION
    public function createTransaction(array $data): FareTransaction
    {
        return DB::transaction(function () use ($data) {

            // Find passenger card
            $card = PassengerCard::lockForUpdate()
                ->findOrFail($data['passenger_card_id']);

            // Find jeepney
            $jeepney = Jeepney::findOrFail(
                $data['jeepney_id']
            );

            // Find boarding and alighting stages
            $boarding = RouteStage::findOrFail(
                $data['boarding_stage_id']
            );

            $alighting = RouteStage::findOrFail(
                $data['alighting_stage_id']
            );

            // Uppercase RFID card number
            $card->passenger_card_no = strtoupper(
                trim($card->passenger_card_no)
            );

            $card->save();

            // Uppercase jeepney plate number
            $jeepney->jeepney_plate = strtoupper(
                trim($jeepney->jeepney_plate)
            );

            $jeepney->save();

            // Validate trip
            $this->validateTrip(
                $jeepney,
                $boarding,
                $alighting
            );

            // Calculate fare based on distance
            $fare = $this->calculateFare(
                (float) $data['distance_km'],
                $card->card_type
            );

            // Get current balance
            $balanceBefore = (float) $card->card_balance;

            // Check sufficient balance
            if ($balanceBefore < $fare) {
                throw ValidationException::withMessages([
                    'passenger_card_id' => 'Insufficient passenger card balance.',
                ]);
            }

            // Deduct fare
            $balanceAfter = round($balanceBefore - $fare, 2);

            $card->card_balance = $balanceAfter;
            $card->save();

            // Create transaction record
            return FareTransaction::create([
                'passenger_card_id' => $card->id,
                'jeepney_id' => $jeepney->id,
                'boarding_stage_id' => $boarding->id,
                'alighting_stage_id' => $alighting->id,
                'distance_km' => $data['distance_km'],
                'calculated_fare' => $fare,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tapped_in_at' => $data['tapped_in_at'],
                'tapped_out_at' => $data['tapped_out_at'],
            ]);
        });
    }

    // UPDATE FARE TRANSACTION
    public function updateTransaction(
        int $id,
        array $data
    ): FareTransaction {

        return DB::transaction(function () use ($id, $data) {

            // Find existing transaction
            $transaction = FareTransaction::lockForUpdate()
                ->findOrFail($id);

            // Lock both affected passenger cards
            $cardIds = array_unique([
                (int) $transaction->passenger_card_id,
                (int) $data['passenger_card_id'],
            ]);

            sort($cardIds);

            $cards = PassengerCard::whereIn('id', $cardIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $oldCard = $cards->get(
                $transaction->passenger_card_id
            );

            $newCard = $cards->get(
                $data['passenger_card_id']
            );

            if (! $oldCard || ! $newCard) {
                throw ValidationException::withMessages([
                    'passenger_card_id' => 'Passenger card not found.',
                ]);
            }

            // Restore previous fare to original card
            $oldCard->card_balance = round(
                (float) $oldCard->card_balance
                + (float) $transaction->calculated_fare,
                2
            );

            $oldCard->save();

            // Find updated jeepney and stages
            $jeepney = Jeepney::findOrFail(
                $data['jeepney_id']
            );

            $boarding = RouteStage::findOrFail(
                $data['boarding_stage_id']
            );

            $alighting = RouteStage::findOrFail(
                $data['alighting_stage_id']
            );

            // Validate trip
            $this->validateTrip(
                $jeepney,
                $boarding,
                $alighting
            );

            // Uppercase RFID number
            $newCard->passenger_card_no = strtoupper(
                trim($newCard->passenger_card_no)
            );

            $newCard->save();

            // Uppercase plate number
            $jeepney->jeepney_plate = strtoupper(
                trim($jeepney->jeepney_plate)
            );

            $jeepney->save();

            // Recalculate fare
            $fare = $this->calculateFare(
                (float) $data['distance_km'],
                $newCard->card_type
            );

            // Check balance
            $balanceBefore = (float) $newCard->card_balance;

            if ($balanceBefore < $fare) {
                throw ValidationException::withMessages([
                    'passenger_card_id' => 'Insufficient passenger card balance.',
                ]);
            }

            // Deduct updated fare
            $balanceAfter = round(
                $balanceBefore - $fare,
                2
            );

            $newCard->card_balance = $balanceAfter;
            $newCard->save();

            // Update transaction record
            $transaction->update([
                'passenger_card_id' => $newCard->id,
                'jeepney_id' => $jeepney->id,
                'boarding_stage_id' => $boarding->id,
                'alighting_stage_id' => $alighting->id,
                'distance_km' => $data['distance_km'],
                'calculated_fare' => $fare,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tapped_in_at' => $data['tapped_in_at'],
                'tapped_out_at' => $data['tapped_out_at'],
            ]);

            return $transaction->fresh();
        });
    }
}
