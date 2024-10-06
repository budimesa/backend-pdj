<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Inventory;
class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Inventory::query();
        
        // Apply global filter
        if ($request->has('filters.global.value')) {
            $query->where('description', 'like', '%' . $request->input('filters.global.value') . '%')
                ->orWhere('notes', 'like', '%' . $request->input('filters.global.value') . '%');
        }

        // Apply specific filters
        if ($request->has('filters.description.value')) {
            $query->where('description', 'like', $request->input('filters.description.value') . '%');
        }

        if ($request->has('filters.notes.value')) {
            $query->where('notes', 'like', $request->input('filters.notes.value') . '%');
        }

        $query->with('incomingItem');
        $query->with('creator');
        $query->orderBy('created_at', 'desc'); 
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($results->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'incoming_item_code' => $item->incomingItem ? $item->incomingItem->incoming_item_code : null,
                'supplier_name' => $item->supplier ? $item->supplier->supplier_name : null,
                'description' => $item->description,
                'notes' => $item->notes,
                'creator' => $item->creator ? $item->creator->name : null,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                // 'invoice_files' => $item->invoice_files,
            ];
        });

        return response()->json([
            'data' => $data,
            'total' => $results->total(),
            'per_page' => $results->perPage(),
            'current_page' => $results->currentPage(),
            'from' => $results->firstItem(),
            'last_page' => $results->lastPage(),
            'offset' => $offset
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
