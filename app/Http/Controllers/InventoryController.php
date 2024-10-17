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

        if ($request->has('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->input('from_warehouse_id'));
        }
        
        // Apply global filter
        if ($request->has('filters.global.value')) {
            $query->where('description', 'like', '%' . $request->input('filters.global.value') . '%')
                ->orWhere('batch_code', 'like', '%' . $request->input('filters.global.value') . '%')
                ->orWhere('notes', 'like', '%' . $request->input('filters.global.value') . '%');
        }

        // Apply specific filters
        if ($request->has('filters.description.value')) {
            $query->where('description', 'like', $request->input('filters.description.value') . '%');
        }

        if ($request->has('filters.batch_code.value')) {
            $query->where('batch_code', 'like', $request->input('filters.batch_code.value') . '%');
        }

        if ($request->has('filters.notes.value')) {
            $query->where('notes', 'like', $request->input('filters.notes.value') . '%');
        }

        $query->with('incomingItem');
        $query->with('item');
        $query->with('batch');
        $query->with('warehouse');
        $query->orderBy('created_at', 'desc'); 
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($results->items())->map(function ($item) {
            return [
                'inventory_id' => $item->id,
                'item_id' => $item->item ? $item->item->id : null,
                'incoming_item_id' => $item->incomingItem ? $item->incomingItem->id : null,
                'incoming_item_code' => $item->incomingItem ? $item->incomingItem->incoming_item_code : null,
                'concat_code_name' => $item->item->item_code . ' - ' . $item->item->item_name,
                'concat_inventory' => '[ ' . $item->incomingItem->incoming_item_code . ' ] - [ ' . $item->batch->batch_code . ' ] - [ ' . 
                $item->item->item_code . ' - ' . $item->item->item_name . ' @' . $item->net_weight . ' KG ] ' . ' ( ' . $item->actual_stock . ' Dus )',
                'batch_id' => $item->batch ? $item->batch->id: null,
                'batch_code' => $item->batch ? $item->batch->batch_code : null,
                'warehouse_name' => $item->warehouse->pluck('warehouse_name')->first(),
                'description' => $item->description,
                'barcode_number' => $item->barcode_number,
                'gross_weight' => $item->gross_weight,
                'net_weight' => $item->net_weight,
                'unit_price' => $item->unit_price,
                'initial_stock' => $item->initial_stock,
                'available_stock' => $item->available_stock,
                'actual_stock' => $item->actual_stock,
                'total_price' => $item->total_price,
                'labor_cost' => $item->labor_cost,
                'expiry_date' => $item->expiry_date,
                'transaction_type' => $item->transaction_type,
                'price_status' => $item->price_status,
                'notes' => $item->notes,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
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
