<?php

namespace App\Http\Controllers;

use App\Models\Jeepney;
use App\Services\JeepneyService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JeepneyController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Jeepney::query()
                ->with('route')
                ->orderBy('jeepney_plate')
                ->paginate(25),
        ]);
    }

    public function store(Request $request, JeepneyService $jeepneyService)
    {
        $request->merge([
            'jeepney_plate' => strtoupper(trim((string) $request->input('jeepney_plate'))),
        ]);

        $validated = $request->validate([
            'jeepney_plate' => ['required', 'string', 'max:255', 'unique:jeepneys,jeepney_plate'],
            'route_id' => ['required', 'integer', 'exists:routes,id'],
        ]);

        $jeepney = $jeepneyService->createJeepney($validated);

        return response()->json([
            'data' => $jeepney->load('route'),
        ], 201);
    }

    public function show(Jeepney $jeepney)
    {
        return response()->json([
            'data' => $jeepney->load('route'),
        ]);
    }

    public function update(Request $request, Jeepney $jeepney, JeepneyService $jeepneyService)
    {
        $request->merge([
            'jeepney_plate' => strtoupper(trim((string) $request->input(
                'jeepney_plate',
                $jeepney->jeepney_plate
            ))),
            'route_id' => $request->input('route_id', $jeepney->route_id),
        ]);

        $validated = $request->validate([
            'jeepney_plate' => [
                'required',
                'string',
                'max:255',
                Rule::unique('jeepneys', 'jeepney_plate')->ignore($jeepney->id),
            ],
            'route_id' => ['required', 'integer', 'exists:routes,id'],
        ]);

        if (
            (int) $validated['route_id'] !== (int) $jeepney->route_id
            && $jeepney->fareTransactions()->exists()
        ) {
            return response()->json([
                'message' => 'A jeepney with fare transactions cannot be assigned to another route.',
            ], 409);
        }

        $jeepney = $jeepneyService->updateJeepney($jeepney->id, $validated);

        return response()->json([
            'data' => $jeepney->load('route'),
        ]);
    }

    public function destroy(Jeepney $jeepney)
    {
        if ($jeepney->fareTransactions()->exists()) {
            return response()->json([
                'message' => 'A jeepney with fare transactions cannot be deleted.',
            ], 409);
        }

        $jeepney->delete();

        return response()->noContent();
    }
}
