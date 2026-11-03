<?php

namespace App\Http\Controllers;

use App\Models\PassengerCard;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PassengerCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => PassengerCard::query()
                ->orderBy('passenger_card_no')
                ->paginate(25),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'passenger_card_no' => strtoupper(trim((string) $request->input('passenger_card_no'))),
        ]);

        $validated = $request->validate([
            'passenger_card_no' => ['required', 'string', 'max:255', 'unique:passenger_cards,passenger_card_no'],
            'card_type' => ['required', Rule::in(['Regular', 'Student', 'Senior'])],
            'card_balance' => ['sometimes', 'required', 'numeric', 'min:0', 'decimal:0,2'],
        ]);

        $passengerCard = PassengerCard::create($validated);

        return response()->json([
            'data' => $passengerCard,
        ], 201);
    }

    public function show(PassengerCard $passengerCard)
    {
        return response()->json([
            'data' => $passengerCard->loadCount('fareTransactions'),
        ]);
    }

    public function update(Request $request, PassengerCard $passengerCard)
    {
        $request->merge([
            'passenger_card_no' => strtoupper(trim((string) $request->input(
                'passenger_card_no',
                $passengerCard->passenger_card_no
            ))),
            'card_type' => $request->input('card_type', $passengerCard->card_type),
        ]);

        $validated = $request->validate([
            'passenger_card_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('passenger_cards', 'passenger_card_no')->ignore($passengerCard->id),
            ],
            'card_type' => ['required', Rule::in(['Regular', 'Student', 'Senior'])],
        ]);

        $passengerCard->update($validated);

        return response()->json([
            'data' => $passengerCard->fresh(),
        ]);
    }

    public function destroy(PassengerCard $passengerCard)
    {
        if ($passengerCard->fareTransactions()->exists()) {
            return response()->json([
                'message' => 'A passenger card with fare transactions cannot be deleted.',
            ], 409);
        }

        $passengerCard->delete();

        return response()->noContent();
    }
}
