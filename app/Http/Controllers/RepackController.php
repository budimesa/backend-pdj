<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use App\Models\Repack;
use App\Models\RepackDetail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RepackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'repack_date' => 'required',
            'warehouse_id' => 'required',
            'quantity' => 'required',
            'repack_weight' => 'required',
            'details' => 'required|array',
            'details.*.item_id' => 'required',
            'details.*.net_weight' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
            'details.*.expiry_date' => 'nullable|date',
        ]);

        $details = $request->input('details'); // Dapatkan details ke dalam variabel lokal
        // Mulai transaction
        DB::transaction(function () use ($request, $details) {

            $inventory = Inventory::find($request->inventory_id);

            if ($inventory && $inventory->available_stock > 0 && $inventory->actual_stock > 0) {
                try {
                    $inventory->update([                
                        'available_stock' => $inventory->available_stock - 1,
                        'actual_stock' => $inventory->actual_stock - 1,
                        'updated_by' => Auth::id(),
                    ]);
                } catch (\Exception $e) {
                    // Tangani kesalahan, bisa log atau return response error
                    return response()->json(['error' => 'Gagal mengupdate inventory.'], 500);
                }
            } else {
                return response()->json(['error' => 'Stok tidak mencukupi.'], 400);
            }
            $repackDate = Carbon::parse($request->repack_date)->format('Y-m-d H:i:s');
            $repack = Repack::create([
                'repack_date' => $repackDate,
                'source_inventory_details' => $request->source_inventory_details,
                'repack_type' => $request->from_warehouse_id,                                
                'created_by' => Auth::id(),
            ]);

            // foreach ($details as $index => $detail) {
            //     if(isset($detail['inventory_id'])) {
            //         $existInventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
            //             ->where('warehouse_id', $request->to_warehouse_id)
            //             ->first();
            //         if($existInventoryDetail) {
            //             $existInventoryDetail->update([
            //                 'quantity' => $existInventoryDetail->quantity + $detail['quantity'],
            //             ]);
            //         }
            //         else {
            //             InventoryDetail::create([
            //                 'inventory_id'   => $detail['inventory_id'],
            //                 'warehouse_id'   => $request->to_warehouse_id,
            //                 'quantity'       => $detail['quantity'],
            //                 'created_by' => Auth::id(),
            //             ]);
            //         }

            //         $inventoryDetail = InventoryDetail::where('inventory_id', $detail['inventory_id'])
            //             ->where('warehouse_id', $request->from_warehouse_id)
            //             ->first();

            //             $inventoryDetail->update([
            //                 'quantity' => $inventoryDetail->quantity - $detail['quantity'],
            //             ]);

            //         ItemTransferDetail::create([
            //             'item_transfer_id' => $itemTransfer->id,
            //             'inventory_id'   => $detail['inventory_id'],
            //             'quantity'       => $detail['quantity'],
            //             'created_by' => Auth::id(),
            //         ]);
            //     }
            // }
        });

        return response()->json(['message' => 'Data inserted successfully'], 201);
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
