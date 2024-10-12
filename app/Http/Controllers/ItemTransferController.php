<?php

namespace App\Http\Controllers;

use App\Models\ItemTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ItemTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ItemTransfer::with(['inventories', 'fromWarehouse', 'toWarehouse'])->get();
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
            'total_price' => 'required|numeric',
            'transfer_status' => 'required',
            'details' => 'required|array',
            'details.*.item_id' => 'required',
            'details.*.gross_weight' => 'required|numeric',
            'details.*.net_weight' => 'required|numeric',
            'details.*.unit_price' => 'required|numeric',
            'details.*.initial_stock' => 'required|numeric',
            'details.*.total_price' => 'required|numeric',
            'details.*.labor_cost' => 'required|numeric',
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
                'total_price' => $request->total_price,
                'transfer_status' => $request->transfer_status,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($details as $index => $detail) {
                if(isset($detail['id'])) {
                    $inventory = Inventory::where('incoming_item_id', $incomingItem->id)
                    ->where('id', $detail['id'])
                    ->first();
                    // update data inventory yang ada
                    $inventory->update([
                        'available_stock' => $detail['initial_stock'],
                        'actual_stock' => $detail['initial_stock'],
                    ]);
                    //  insert data inventory untuk barang transfer
                    Inventory::create([
                        'item_transfer_id' => $itemTransfer->id,
                        'item_id' => $detail['item_id'],
                        'batch_id' => $detail['batch_id'],
                        'warehouse_id' => $detail['to_warehouse_id'],
                        'barcode_number' => $detail['barcode_number'],
                        'description' => $detail['description'],
                        'gross_weight' => $detail['gross_weight'],
                        'net_weight' => $detail['net_weight'],
                        'unit_price' => $detail['unit_price'],
                        'initial_stock' => $detail['initial_stock'],
                        'available_stock' => $detail['initial_stock'],
                        'actual_stock' => $detail['initial_stock'],
                        'total_price' => $detail['total_price'],
                        'labor_cost' => $detail['labor_cost'],
                        'notes' => '',
                        'transaction_type' => 2, // Transfer
                        // 'expiry_date' => $detail['expiry_date'],                    
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        });

        return response()->json($itemTransfer, 201);
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
