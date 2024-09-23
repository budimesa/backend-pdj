<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    // Get all warehouses
    public function index()
    {
        return Warehouse::all();
    }

    // Create a new warehouse
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_code' => 'required|unique:warehouses,warehouse_code',
            'warehouse_name' => 'required',
            'location' => 'required',
        ]);

        $validated['created_by'] = Auth::id();

        $warehouse = Warehouse::create($validated);

        return response()->json($warehouse, 201);
    }

    // Get single warehouse
    public function show(Warehouse $warehouse)
    {
        return $warehouse;
    }

    // Update warehouse
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'warehouse_code' => 'required|unique:warehouses,warehouse_code,' . $warehouse->id,
            'warehouse_name' => 'required',
            'location' => 'required',
        ]);

        $validated['updated_by'] = Auth::id();

        $warehouse->update($validated);

        return response()->json($warehouse, 200);
    }

    // Delete warehouse
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json(['message' => 'Warehouse deleted'], 204);
    }
}
