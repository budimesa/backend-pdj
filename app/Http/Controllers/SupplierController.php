<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    // Get all suppliers
    public function index()
    {
        return Supplier::all();
    }

    // Create a new supplier
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_type' => 'required|in:regular,non-regular',
            'supplier_code' => 'required|unique:suppliers,supplier_code',
            'supplier_name' => 'required',
            'phone_number' => 'nullable',
            'address' => 'nullable',
        ]);

        $validated['created_by'] = Auth::id();

        $supplier = Supplier::create($validated);

        return response()->json($supplier, 201);
    }

    // Get single supplier
    public function show(Supplier $supplier)
    {
        return $supplier;
    }

    // Update supplier
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_type' => 'required|in:regular,non-regular',
            'supplier_code' => 'required|unique:suppliers,supplier_code,' . $supplier->id,
            'supplier_name' => 'required',
            'phone_number' => 'nullable',
            'address' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $supplier->update($validated);

        return response()->json($supplier, 200);
    }

    // Delete supplier
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted'], 204);
    }
}
