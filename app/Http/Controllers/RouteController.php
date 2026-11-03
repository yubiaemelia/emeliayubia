<?php

namespace App\Http\Controllers;

use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'data' => Route::query()
                ->withCount(['jeepneys', 'routeStages'])
                ->orderBy('route_code')
                ->paginate(25),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_code' => ['required', 'string', 'max:255', 'unique:routes,route_code'],
            'route_name' => ['required', 'string', 'max:255'],
        ]);

        $route = Route::create($validated);

        return response()->json([
            'data' => $route,
        ], 201);
    }

    public function show(Route $route)
    {
        return response()->json([
            'data' => $route->load(['jeepneys', 'routeStages']),
        ]);
    }

    public function update(Request $request, Route $route)
    {
        $request->merge([
            'route_code' => $request->input('route_code', $route->route_code),
            'route_name' => $request->input('route_name', $route->route_name),
        ]);

        $validated = $request->validate([
            'route_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('routes', 'route_code')->ignore($route->id),
            ],
            'route_name' => ['required', 'string', 'max:255'],
        ]);

        $route->update($validated);

        return response()->json([
            'data' => $route->fresh(),
        ]);
    }

    public function destroy(Route $route)
    {
        if ($route->jeepneys()->exists() || $route->routeStages()->exists()) {
            return response()->json([
                'message' => 'A route with jeepneys or stages cannot be deleted.',
            ], 409);
        }

        $route->delete();

        return response()->noContent();
    }
}
