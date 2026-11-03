<?php

namespace App\Http\Controllers;

use App\Models\FareTransaction;
use App\Services\FareTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FareTransactionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => FareTransaction::query()
                ->with([
                    'passengerCard',
                    'jeepney',
                    'boardingStage',
                    'alightingStage',
                ])
                ->latest()
                ->paginate(25),
        ]);
    }

    public function store(Request $request, FareTransactionService $fareTransactionService): JsonResponse
    {
        $validated = $this->validatedData($request);

        $transaction = $fareTransactionService->createTransaction($validated);

        return $this->transactionResponse($transaction, 201);
    }

    public function show(FareTransaction $fareTransaction): JsonResponse
    {
        return $this->transactionResponse($fareTransaction);
    }

    public function update(
        Request $request,
        FareTransaction $fareTransaction,
        FareTransactionService $fareTransactionService
    ): JsonResponse {
        $request->merge([
            'passenger_card_id' => $request->input('passenger_card_id', $fareTransaction->passenger_card_id),
            'jeepney_id' => $request->input('jeepney_id', $fareTransaction->jeepney_id),
            'boarding_stage_id' => $request->input('boarding_stage_id', $fareTransaction->boarding_stage_id),
            'alighting_stage_id' => $request->input('alighting_stage_id', $fareTransaction->alighting_stage_id),
            'distance_km' => $request->input('distance_km', $fareTransaction->distance_km),
            'tapped_in_at' => $request->input(
                'tapped_in_at',
                $fareTransaction->tapped_in_at->toDateTimeString()
            ),
            'tapped_out_at' => $request->input(
                'tapped_out_at',
                $fareTransaction->tapped_out_at->toDateTimeString()
            ),
        ]);

        $validated = $this->validatedData($request);
        $transaction = $fareTransactionService->updateTransaction($fareTransaction->id, $validated);

        return $this->transactionResponse($transaction);
    }

    public function destroy(FareTransaction $fareTransaction): JsonResponse
    {
        return response()->json([
            'message' => 'Fare transactions are immutable and cannot be deleted.',
        ], 409);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'passenger_card_id' => ['required', 'integer', 'exists:passenger_cards,id'],
            'jeepney_id' => ['required', 'integer', 'exists:jeepneys,id'],
            'boarding_stage_id' => ['required', 'integer', 'exists:route_stages,id'],
            'alighting_stage_id' => [
                'required',
                'integer',
                'different:boarding_stage_id',
                'exists:route_stages,id',
            ],
            'distance_km' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'tapped_in_at' => ['required', 'date'],
            'tapped_out_at' => ['required', 'date', 'after:tapped_in_at'],
        ]);
    }

    private function transactionResponse(
        FareTransaction $transaction,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'data' => $transaction->load([
                'passengerCard',
                'jeepney',
                'boardingStage',
                'alightingStage',
            ]),
        ], $status);
    }
}
