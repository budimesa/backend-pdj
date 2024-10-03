<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
class IncomingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return IncomingItem::all();
    }

    public function getLastItem()
    {
        $lastItem = IncomingItem::latest()->first();

        if ($lastItem) {
            return response()->json($lastItem, 200);
        }

        return response()->json(['message' => 'No items found'], 205);
    }

    /**
     * Store a newly created resource in storage.
     */

    private function generateBarcodeNumber() {
        // Menghasilkan 12 digit nomor acak
        return str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
    }
    public function store(Request $request) {
        $request->validate([
            'incoming_item_code' => 'required|unique:incoming_items,incoming_item_code',
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
            'shipment_date' => 'required',
            'received_date' => 'required',
            'total_item_price' => 'required',
            'shipping_cost' => 'nullable',
            'labor_cost' => 'nullable',
            'other_fee' => 'nullable',
            'total_cost' => 'required',
            'notes' => 'nullable',
            'invoice_files' => 'nullable',
            'details' => 'required|array',
            'details.*.item_id' => 'required|string',
            // 'details.*.batch_id' => 'required|string',
            'details.*.barcode_number' => 'required|numeric',
            'details.*.gross_weight' => 'required|numeric',
            'details.*.net_weight' => 'required|numeric',
            'details.*.unit_price' => 'required|numeric',
            'details.*.actual_stock' => 'required|numeric',
            'details.*.total_price' => 'required|numeric',
            'details.*.labor_cost' => 'required|numeric',
            'details.*.notes' => 'nullable|string',
            'details.*.expiry_date' => 'nullable|date',
            'details.*.transaction_type' => 'nullable|string',
        ]);

        // Mulai transaction
        DB::transaction(function () use ($request) {
            // Insert ke tabel mst_bom_formula
            $incomingItem = IncomingItem::create([
                'incoming_item_code' => $request->incoming_item_code,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'shipment_date' => $request->shipment_date,
                'received_date' => $request->received_date,
                'total_item_price' => $request->total_item_price,
                'shipping_cost' => $request->shipping_cost,
                'labor_cost' => $request->labor_cost,
                'other_fee' => $request->other_fee,
                'total_cost' => $request->total_cost,
                'notes' => $request->notes,
                'invoice_files' => $request->invoice_files,
                'created_by' => Auth::id(),
            ]);
            
            foreach ($request->details as $detail) {
                
                $batchController = new BatchController();
                $batchCode = $batchController->generateBatchCode($request->supplier_id);
                
                // Menyimpan batch baru dan mendapatkan batch_id
                $batch = Batch::create([
                    'batch_code' => $batchCode,
                    'supplier_id' => $request->supplier_id,
                    'batch_code_type' => 'incoming', // Atur sesuai dengan kebutuhan
                ]);
                
                $barcodeNumber = $this->generateBarcodeNumber();
                Inventory::create([
                    'incoming_item_id' => $incomingItem->id,
                    'item_id' => $detail['item_id'],
                    'batch_id' => $detail['batch_id'],
                    'barcode_number' => $barcodeNumber,
                    'gross_weight' => $detail['gross_weight'],
                    'net_weight' => $detail['net_weight'],
                    'unit_price' => $detail['unit_price'],
                    'initial_stock' => $detail['actual_stock'],
                    'available_stock' => $detail['actual_stock'],
                    'actual_stock' => $detail['actual_stock'],
                    'total_price' => $detail['total_price'],
                    'labor_cost' => $detail['labor_cost'],
                    'notes' => $detail['notes'],
                    // 'expiry_date' => $detail['expiry_date'],
                    'transaction_type' => 'incoming',
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return response()->json(['message' => 'Data inserted successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(IncomingItem $incomingItem)
    {
        return $incomingItem;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IncomingItem $incomingItem)
    {
        $validated = $request->validate([
            'incoming_item_code' => 'required|unique:incoming_items,incoming_item_code,' . $incomingItem->id,
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
            'shipment_date' => 'required',
            'received_date' => 'required',
            'total_item_price' => 'required',
            'shipping_cost' => 'nullable',
            'labor_cost' => 'nullable',
            'other_fee' => 'nullable',
            'total_cost' => 'required',
            'notes' => 'nullable',
            'invoice_files' => 'nullable',
        ]);

        $validated['updated_by'] = Auth::id();

        $incomingItem->update($validated);

        return response()->json($incomingItem, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingItem $incomingItem)
    {
        $incomingItem->delete();

        return response()->json(['message' => 'Incoming Item deleted'], 204);
    }
}
