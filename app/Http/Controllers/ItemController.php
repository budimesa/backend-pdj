<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Item::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|unique:items,item_code',
            'item_name' => 'required',
            'notes' => 'nullable',
            'sale_unit' => 'required|in:Dus,Keranjang,Karung,Plastik',
        ]);

        $validated['created_by'] = Auth::id();

        $item = Item::create($validated);

        return response()->json($item, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return $item;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'item_code' => 'required|unique:items,item_code,' . $item->id,
            'item_name' => 'required',
            'notes' => 'nullable',
            'sale_unit' => 'required|in:Dus,Keranjang,Karung,Plastik',
        ]);

        $validated['updated_by'] = Auth::id();

        $item->update($validated);

        return response()->json($item, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return response()->json(['message' => 'Item deleted'], 204);
    }
}
