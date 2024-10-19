<?php

namespace App\Http\Controllers;

use App\Models\InventoryDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InventoryDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InventoryDetail::query();

        // Filter berdasarkan inventory_id jika ada
        if ($request->has('from_warehouse_id')) {
            $query->where('warehouse_id', $request->input('from_warehouse_id'));
        }

        // Global filter
        if ($request->has('filters.global.value')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->input('filters.global.value') . '%');
            });
        }

        $query->with(['inventory.incomingItem' , 'inventory.batch', 'warehouse']);
        $query->orderBy('created_at', 'desc');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($results->items())->map(function ($item) {
            $inventory = $item->inventory;
        
            return [
                'inventory_id' => $item->inventory_id,
                'item_id' => $inventory && $inventory->item ? $inventory->item->id : null,
                'incoming_item_id' => $inventory && $inventory->incomingItem ? $inventory->incomingItem->id : null,
                'incoming_item_code' => $inventory && $inventory->incomingItem ? $inventory->incomingItem->incoming_item_code : null,
                'concat_code_name' => $inventory && $inventory->item ? $inventory->item->item_code . ' - ' . $inventory->item->item_name : null,
                'concat_inventory' => $inventory && $inventory->incomingItem ? '[ ' . $inventory->incomingItem->incoming_item_code . ' ] - [ ' . $inventory->batch->batch_code . ' ] - [ ' . 
                $inventory->item->item_code . ' - ' . $inventory->item->item_name . ' @' . $inventory->net_weight . ' KG ] ' . ' ( ' . $item->quantity . ' Dus )' : null,
                'batch_id' => $inventory && $inventory->batch ? $inventory->batch->id : null,
                'batch_code' => $inventory && $inventory->batch ? $inventory->batch->batch_code : null,
                'warehouse_name' => $item->warehouse->warehouse_name,
                'description' => $inventory ? $inventory->description : null,
                'barcode_number' => $inventory ? $inventory->barcode_number : null,
                'gross_weight' => $inventory ? $inventory->gross_weight : null,
                'net_weight' => $inventory ? $inventory->net_weight : null,
                'unit_price' => $inventory ? $inventory->unit_price : null,
                'initial_stock' => $inventory ? $inventory->initial_stock : null,
                'available_stock' => $inventory ? $inventory->available_stock : null,
                'actual_stock' => $inventory ? $inventory->actual_stock : null,
                'quantity' => $item->quantity,
                'total_price' => $inventory ? $inventory->unit_price * $item->quantity : null,
                'labor_cost' => $inventory ? $inventory->labor_cost * $item->quantity : null,
                'expiry_date' => $inventory ? $inventory->expiry_date : null,
                'transaction_type' => $inventory ? $inventory->transaction_type : null,
                'price_status' => $inventory ? $inventory->price_status : null,
                'notes' => $inventory ? $inventory->notes : null,
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
