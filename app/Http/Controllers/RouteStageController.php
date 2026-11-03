<?php

namespace App\Http\Controllers;

use App\Models\RouteStage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'route_id' => ['sometimes', 'integer', 'exists:routes,id'],
        ]);

        return response()->json([
            'data' => RouteStage::query()
                ->with('route')
                ->when(
                    $filters['route_id'] ?? null,
                    fn ($query, $routeId) => $query->where('route_id', $routeId)
                )
                ->orderBy('route_id')
                ->orderBy('stage_order')
                ->paginate(25),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'stage_name' => ['required', 'string', 'max:255'],
            'stage_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('route_stages', 'stage_order')
                    ->where('route_id', $request->input('route_id')),
            ],
        ]);

        $routeStage = RouteStage::create($validated);

        return response()->json([
            'data' => $routeStage->load('route'),
        ], 201);
    }

    public function show(RouteStage $routeStage)
    {
        return response()->json([
            'data' => $routeStage->load('route'),
        ]);
    }

    public function update(Request $request, RouteStage $routeStage)
    {
        $request->merge([
            'route_id' => $request->input('route_id', $routeStage->route_id),
            'stage_name' => $request->input('stage_name', $routeStage->stage_name),
            'stage_order' => $request->input('stage_order', $routeStage->stage_order),
        ]);

        $validated = $request->validate([
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'stage_name' => ['required', 'string', 'max:255'],
            'stage_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('route_stages', 'stage_order')
                    ->where('route_id', $request->input('route_id'))
                    ->ignore($routeStage->id),
            ],
        ]);

        if (
            ((int) $validated['route_id'] !== (int) $routeStage->route_id
                || (int) $validated['stage_order'] !== (int) $routeStage->stage_order)
            && (
                $routeStage->boardingTransactions()->exists()
                || $routeStage->alightingTransactions()->exists()
            )
        ) {
            return response()->json([
                'message' => 'A stage used by fare transactions cannot change route or order.',
            ], 409);
        }

        $routeStage->update($validated);

        return response()->json([
            'data' => $routeStage->fresh()->load('route'),
        ]);
    }

    public function destroy(RouteStage $routeStage)
    {
        if (
            $routeStage->boardingTransactions()->exists()
            || $routeStage->alightingTransactions()->exists()
        ) {
            return response()->json([
                'message' => 'A stage referenced by fare transactions cannot be deleted.',
            ], 409);
        }

        $routeStage->delete();

        return response()->noContent();
    }
}
