<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryDetail;
use App\Models\Repack;
use App\Models\SourceRepack;
use App\Models\TargetRepack;
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
            'repack_weight' => 'required',
            'repack_leftover' => 'required',
            'details' => 'required|array',
            'details.*.item_id' => 'required',
            'details.*.inventory_id' => 'required', // Pastikan inventory_id ada
            'details.*.net_weight' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
            'details.*.expiry_date' => 'nullable|date',
        ]);

        $details = $request->input('details');

        if (empty($details) || !isset($details[0]['inventory_id'])) {
            return response()->json(['error' => 'Detail atau inventory_id tidak ditemukan.'], 400);
        }

        $detail = $details[0]; // Ambil detail pertama

        // Mulai transaksi
        DB::transaction(function () use ($request, $detail) {
            // Gunakan notasi kurung siku untuk mengakses inventory_id
            $inventory = Inventory::find($detail['inventory_id']); 

            if ($inventory && $inventory->available_stock > 0 && $inventory->actual_stock > 0) {
                try {
                    $inventory->update([                
                        'available_stock' => $inventory->available_stock - 1,
                        'actual_stock' => $inventory->actual_stock - 1,
                        'updated_by' => Auth::id(),
                    ]);

                    $repackInventory = $this->createNewInventory($inventory, $request->repack_weight, 2, 0);
                    $leftoverRepackInventory = $this->createNewInventory($inventory, $request->repack_leftover, 2, 1);

                    $repackDate = Carbon::parse($request->repack_date)->format('Y-m-d H:i:s');
                    $repack = Repack::create([
                        'repack_date' => $repackDate,
                        'repack_type' => 1,                                
                        'created_by' => Auth::id(),
                    ]);

                    $sourceRepack = SourceRepack::create([
                        'repack_id' => $repack->id,
                        'inventory_d_id' => $detail['inventory_detail_id'],
                        'quantity' => 1,
                        'created_by' => Auth::id(),
                    ]);

                    $targetRepack1 = TargetRepack::create([
                        'repack_id' => $repack->id,
                        'inventory_d_id' => $repackInventory->id,
                        'quantity' => 1,
                        'created_by' => Auth::id(),
                    ]);

                    $targetRepack2 = TargetRepack::create([
                        'repack_id' => $repack->id,
                        'inventory_d_id' => $leftoverRepackInventory->id,
                        'quantity' => 1,
                        'created_by' => Auth::id(),
                    ]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Gagal mengupdate inventory.'], 500);
                }
            } else {
                return response()->json(['error' => 'Stok tidak mencukupi.'], 400);
            }            
        });

        return response()->json(['message' => 'Data inserted successfully'], 201);
    }

    /**
     * Display the specified resource.
     */

    public function createNewInventory($inventory, $weight, $transactionType, $leftover) {
        $newInventory =  Inventory::create([
            'incoming_item_id' => $inventory->incoming_item_id,
            'item_id' => $inventory->item_id,
            'batch_id' => $inventory->batch_id,
            'description' => $inventory->description,
            'barcode_number' => $inventory->barcode_number,
            'gross_weight' => $weight,
            'net_weight' => $weight,
            'unit_price' => $inventory->unit_price,
            'initial_stock' => 1,
            'available_stock' => 1, // Atau sesuaikan sesuai kebutuhan
            'actual_stock' => 1,
            'total_price' => $inventory->total_price,
            'labor_cost' => $inventory->labor_cost,
            'expiry_date' => $inventory->expiry_date,
            'notes' => $inventory->notes,
            'transaction_type' => $transactionType,
            'price_status' => $inventory->price_status,
            'created_by' => Auth::id(),
        ]);

        $inventoryDetail = InventoryDetail::create([
            'inventory_id' => $newInventory->id,
            'warehouse_id' => 1, // Gudang Sementara (Toko)
            'repack_leftover' => $leftover,
            'quantity' => 1,   
            'created_by' => Auth::id(),                 
        ]);

        return $inventoryDetail;
    }
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
