<?php

namespace App\Http\Controllers;

use App\Models\ItemTransfer;
use App\Models\ItemTransferDetail;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ItemTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ItemTransfer::query();

        // Apply global filter
        if ($request->has('filters.global.value')) {
            $query->where('notes', 'like', '%' . $request->input('filters.global.value') . '%');
        }

        if ($request->has('filters.notes.value')) {
            $query->where('notes', 'like', '%' . $request->input('filters.notes.value') . '%');
        }

        $query->with(['fromWarehouse', 'toWarehouse']);
        $query->orderBy('created_at', 'desc'); 
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);


        $data = collect($results->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'transfer_code' => $item->transfer_code,
                'from_warehouse_name' => $item->fromWarehouse->warehouse_name ?? null,
                'to_warehouse_name' => $item->toWarehouse->warehouse_name ?? null,
                'total_quantity' => $item->total_quantity,
                'total_item_price' => $item->total_item_price,
                'transfer_status' => $item->transfer_status,
                'notes' => $item->notes,
                'creator' => $item->creator ? $item->creator->name : null,
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

    public function getLastItem()
    {
        $lastItem = ItemTransfer::latest()->first();

        if ($lastItem) {
            return response()->json($lastItem, 200);
        }

        return response()->json(['message' => 'No items found'], 205);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transfer_code' => 'required|unique:item_transfers,transfer_code',
            'transfer_date' => 'required',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id', 
            'total_quantity' => 'required|numeric',
            // 'transfer_status' => 'required',
            'details' => 'required|array',
            'details.*.item_id' => 'required',
            'details.*.net_weight' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
            'details.*.expiry_date' => 'nullable|date',
        ]);

        // Menyortir array details berdasarkan item_id
        $details = $request->input('details'); // Dapatkan details ke dalam variabel lokal
        // Mulai transaction
        DB::transaction(function () use ($request, $details) {
            $transferDate = Carbon::parse($request->transfer_date)->format('Y-m-d H:i:s');
            $itemTransfer = ItemTransfer::create([
                'transfer_code' => $request->transfer_code,
                'transfer_date' => $transferDate,                
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'total_quantity' => $request->total_quantity,
                'transfer_status' => 1,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($details as $index => $detail) {
                if(isset($detail['inventory_id'])) {
                    $existInventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
                        ->where('warehouse_id', $request->to_warehouse_id)
                        ->first();
                    if($existInventoryDetail) {
                        $existInventoryDetail->update([
                            'quantity' => $existInventoryDetail->quantity + $detail['quantity'],
                        ]);
                    }
                    else {
                        InventoryDetail::create([
                            'inventory_id'   => $detail['inventory_id'],
                            'warehouse_id'   => $request->to_warehouse_id,
                            'quantity'       => $detail['quantity'],
                            'created_by' => Auth::id(),
                        ]);
                    }

                    $inventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
                        ->where('warehouse_id', $request->from_warehouse_id)
                        ->first();

                        $inventoryDetail->update([
                            'quantity' => $inventoryDetail->quantity - $detail['quantity'],
                        ]);

                    ItemTransferDetail::create([
                        'item_transfer_id' => $itemTransfer->id,
                        'inventory_id'   => $detail['inventory_id'],
                        'quantity'       => $detail['quantity'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Data inserted successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemTransfer $itemTransfer)
    {
        $itemTransfer->load(['fromWarehouse', 'toWarehouse', 'itemTransferDetails', 'inventories.incomingItem', 'inventories.item', 'inventories.batch', 'inventories.warehouse']);

        $details = $itemTransfer->inventories->map(function ($inventory) use ($itemTransfer) {
            // Find the corresponding item transfer detail for the current inventory
            $itemTransferDetail = $itemTransfer->itemTransferDetails->firstWhere('inventory_id', $inventory->id);

            return [
                'inventory_id' => $inventory->id,
                'incoming_item_id' => $inventory->incoming_item_id,
                'incoming_item_code' => $inventory->incomingItem->incoming_item_code,
                'concat_code_name' => $inventory->item->item_code . ' - ' . $inventory->item->item_name,
                'item_id' => $inventory->item_id,
                'batch_id' => $inventory->batch_id,
                'batch_code' => $inventory->batch->batch_code,
                'description' => $inventory->description,
                'barcode_number' => $inventory->barcode_number,
                'gross_weight' => $inventory->gross_weight,
                'net_weight' => $inventory->net_weight,
                'transfer_quantity' => $itemTransferDetail ? $itemTransferDetail->quantity : 0, // Default to 0 if no detail found
                'initial_stock' => $inventory->initial_stock,
                'available_stock' => $inventory->available_stock,
                'actual_stock' => $inventory->actual_stock,
                'expiry_date' => $inventory->expiry_date,
                'notes' => $inventory->notes,
                'transaction_type' => $inventory->transaction_type,
                'price_status' => $inventory->price_status,
            ];
        });

        return response()->json(['item_transfer' => $itemTransfer, 'details' => $details], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'transfer_code' => 'required|unique:item_transfers,transfer_code,' . $id,
            'transfer_date' => 'required',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id', 
            'total_quantity' => 'required|numeric',
            'details' => 'required|array',
            // 'details.*.inventory_id' => 'required|exists:inventories,id',
            'details.*.transfer_quantity' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {
            $transferDate = Carbon::parse($request->transfer_date)->format('Y-m-d H:i:s');
            
            $itemTransfer = ItemTransfer::findOrFail($id);
            $itemTransfer->update([
                'transfer_date' => $transferDate,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'total_quantity' => $request->total_quantity,
                'transfer_status' => 1,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            $existingDetails = ItemTransferDetail::where('item_transfer_id', $id)->get()->keyBy('inventory_id');
            $newDetailIds = collect($request->details)->pluck('inventory_id')->filter();

            // Delete details that are not in the request
            foreach ($existingDetails as $detail) {
                if (!$newDetailIds->contains($detail->inventory_id)) {
                    $detail->delete();
                }
            }

            foreach ($request->details as $detail) {
                $quantityChange = $detail['transfer_quantity'] ?? 0;
                
            if (isset($detail['inventory_id']) && $existingDetails->has($detail['inventory_id'])) {
                $existingDetail = $existingDetails->get($detail['inventory_id']);
                $actualStockDifference = $existingDetail->quantity - $quantityChange;

                // Update stock based on difference
                if ($actualStockDifference !== 0) {
                    $sourceInventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
                        ->where('warehouse_id', $request->from_warehouse_id)
                        ->first();

                    if ($sourceInventoryDetail) {
                        $sourceInventoryDetail->quantity += $actualStockDifference;
                        $sourceInventoryDetail->save();
                    }

                    $sourceInventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
                        ->where('warehouse_id', $request->to_warehouse_id)
                        ->first();
                    
                    if ($sourceInventoryDetail) {
                        $sourceInventoryDetail->quantity -= $actualStockDifference;
                        $sourceInventoryDetail->save();
                    }
                }

                $existingDetail->update([
                    'quantity' => $quantityChange,
                    // 'notes' => $detail['notes'] ?? '',
                    'updated_by' => Auth::id(),
                ]);
            } else {
                InventoryDetail::create([
                    'inventory_id' => $detail['inventory_id'],
                    'warehouse_id' => $request->to_warehouse_id,
                    'quantity' => $quantityChange,
                    'created_by' => Auth::id(),
                ]);

                ItemTransferDetail::create([
                    'item_transfer_id' => $itemTransfer->id,
                    'inventory_id' => $detail['inventory_id'],
                    'quantity' => $quantityChange,
                    'created_by' => Auth::id(),
                ]);
            }
        }
    });

    return response()->json(['message' => 'Data updated successfully'], 200);
}

    /**
     * Remove the specified resource from storage.
     */

    
    public function destroy(string $id)
    {
        //
    }
}
