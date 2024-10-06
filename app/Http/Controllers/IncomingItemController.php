<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItem;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class IncomingItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IncomingItem::query();
        
        // Apply global filter
        if ($request->has('filters.global.value')) {
            $query->where('incoming_item_code', 'like', '%' . $request->input('filters.global.value') . '%')
                ->orWhere('notes', 'like', '%' . $request->input('filters.global.value') . '%');
        }

        // Apply specific filters
        if ($request->has('filters.incoming_item_code.value')) {
            $query->where('incoming_item_code', 'like', $request->input('filters.incoming_item_code.value') . '%');
        }

        if ($request->has('filters.notes.value')) {
            $query->where('notes', 'like', $request->input('filters.notes.value') . '%');
        }

        $query->with('supplier');
        $query->with('creator');
        $query->orderBy('created_at', 'desc'); 
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);

        $data = collect($results->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'incoming_item_code' => $item->incoming_item_code,
                'supplier_name' => $item->supplier ? $item->supplier->supplier_name : null,
                'shipment_date' => $item->shipment_date,
                'received_date' => $item->received_date,
                'total_item_price' => $item->total_item_price,
                'shipping_cost' => $item->shipping_cost,
                'labor_cost' => $item->labor_cost,
                'other_fee' => $item->other_fee,
                'total_cost' => $item->total_cost,
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
            'details.*.item_id' => 'required',
            // 'details.*.batch_id' => 'required|string',
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
        usort($details, function($a, $b) {
            return $a['item_id'] <=> $b['item_id'];
        });

        // Mulai transaction
        DB::transaction(function () use ($request, $details) {
            $shipmentDate = Carbon::parse($request->shipment_date)->format('Y-m-d H:i:s');
            $receivedDate = Carbon::parse($request->received_date)->format('Y-m-d H:i:s');
            $incomingItem = IncomingItem::create([
                'incoming_item_code' => $request->incoming_item_code,
                'supplier_id' => $request->supplier_id,
                'shipment_date' => $shipmentDate,
                'received_date' => $receivedDate,                
                'total_item_price' => $request->total_item_price,
                'shipping_cost' => $request->shipping_cost,
                'labor_cost' => $request->labor_cost,
                'other_fee' => $request->other_fee,
                'total_cost' => $request->total_cost,
                'notes' => $request->notes,
                'invoice_files' => $request->invoice_files,
                'created_by' => Auth::id(),
            ]);

            $batch = null;

            foreach ($details as $index => $detail) {
                $batch = Batch::where('item_id', $detail['item_id'])
                ->where('incoming_item_id', $incomingItem->id)
                ->first();
                
                // Cek apakah item_id sama dengan item_id sebelumnya
                if ($index > 0 && $batch && $detail['item_id'] == $batch->item_id) {
                    // Ambil batch_id dari batch terakhir yang dibuat
                    $batch_id = $batch->id;
                } else {
                    // Generate batch code baru
                    $batchController = new BatchController();
                    $batchCode = $batchController->generateBatchCode($request->supplier_id);

                    // Menyimpan batch baru dan mendapatkan batch_id
                    $supplier = Supplier::find($request->supplier_id);
                    $batch = Batch::create([
                        'batch_code' => $batchCode,
                        'supplier_id' => $request->supplier_id,
                        'incoming_item_id' => $incomingItem->id,
                        'item_id' => $detail['item_id'],
                        'batch_code_type' => $supplier->supplier_type, // Atur sesuai dengan kebutuhan
                    ]);

                    // Simpan batch_id ke variabel
                    $batch_id = $batch->id;
                }

                $barcodeNumber = $this->generateBarcodeNumber();
                Inventory::create([
                    'incoming_item_id' => $incomingItem->id,
                    'item_id' => $detail['item_id'],
                    'batch_id' => $batch_id,
                    'warehouse_id' => 1, // Gudang Sementara (Toko)
                    'barcode_number' => $barcodeNumber,
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
                    // 'expiry_date' => $detail['expiry_date'],                    
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
        // Eager load inventories and their related items
        $incomingItem->load(['inventories.item']);

        // Map through inventories to concatenate item_code and item_name
        $details = $incomingItem->inventories->map(function ($inventory) {
            return [
                'id' => $inventory->id,
                'incoming_item_id' => $inventory->incoming_item_id,
                'item_id' => $inventory->item_id,
                'batch_id' => $inventory->batch_id,
                'description' => $inventory->description,
                'barcode_number' => $inventory->barcode_number,
                'gross_weight' => $inventory->gross_weight,
                'net_weight' => $inventory->net_weight,
                'unit_price' => $inventory->unit_price,
                'initial_stock' => $inventory->initial_stock,
                'available_stock' => $inventory->available_stock,
                'actual_stock' => $inventory->actual_stock,
                'total_price' => $inventory->total_price,
                'labor_cost' => $inventory->labor_cost,
                'expiry_date' => $inventory->expiry_date,
                'notes' => $inventory->notes,
                'transaction_type' => $inventory->transaction_type,
                'price_status' => $inventory->price_status,
                'created_by' => $inventory->created_by,
                'updated_by' => $inventory->updated_by,
                'created_at' => $inventory->created_at,
                'updated_at' => $inventory->updated_at,
                'name' => $inventory->item->item_code . ' - ' . $inventory->item->item_name,
            ];
        });

        return response()->json([
            'incoming_item' => $incomingItem,
            'details' => $details,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'incoming_item_code' => 'required|unique:incoming_items,incoming_item_code,' . $id,
            'supplier_id' => 'required',
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
    
        // Mulai transaction untuk update
        DB::transaction(function () use ($request, $id) {
            $shipmentDate = Carbon::parse($request->shipment_date)->format('Y-m-d H:i:s');
            $receivedDate = Carbon::parse($request->received_date)->format('Y-m-d H:i:s');
    
            // Update IncomingItem
            $incomingItem = IncomingItem::findOrFail($id);
            $incomingItem->update([
                'incoming_item_code' => $request->incoming_item_code,
                'supplier_id' => $request->supplier_id,
                'shipment_date' => $shipmentDate,
                'received_date' => $receivedDate,                
                'total_item_price' => $request->total_item_price,
                'shipping_cost' => $request->shipping_cost,
                'labor_cost' => $request->labor_cost,
                'other_fee' => $request->other_fee,
                'total_cost' => $request->total_cost,
                'notes' => $request->notes,
                'invoice_files' => $request->invoice_files,
                'updated_by' => Auth::id(),
            ]);

            foreach ($request->details as $detail) {
                // Cek apakah sudah ada inventory untuk incoming_item_id dan item_id tersebut    
                if (isset($detail['id'])) {
                    $inventory = Inventory::where('incoming_item_id', $incomingItem->id)
                    ->where('id', $detail['id'])
                    ->first();
                    // Jika ada, update data inventory yang ada
                    $inventory->update([
                        'gross_weight' => $detail['gross_weight'],
                        'net_weight' => $detail['net_weight'],
                        'unit_price' => $detail['unit_price'],
                        'initial_stock' => $detail['initial_stock'],
                        'available_stock' => $detail['initial_stock'],
                        'actual_stock' => $detail['initial_stock'],
                        'total_price' => $detail['total_price'],
                        'labor_cost' => $detail['labor_cost'],
                        'notes' => $detail['notes'] ?? '',
                        'updated_by' => Auth::id(),
                    ]);
                } 
                else {
                    // Jika tidak ada, buat record baru
                    $batchController = new BatchController();
                    $batchCode = $batchController->generateBatchCode($request->supplier_id);
    
                    $batch = Batch::create([
                        'batch_code' => $batchCode,
                        'supplier_id' => $request->supplier_id,
                        'incoming_item_id' => $incomingItem->id,
                        'item_id' => $detail['item_id'],
                        'batch_code_type' => Supplier::find($request->supplier_id)->supplier_type,
                    ]);
    
                    $barcodeNumber = $this->generateBarcodeNumber();
                    Inventory::create([
                        'incoming_item_id' => $incomingItem->id,
                        'item_id' => $detail['item_id'],
                        'batch_id' => $batch->id,
                        'warehouse_id' => 1,
                        'barcode_number' => $barcodeNumber,
                        'description' => $detail['description'],
                        'gross_weight' => $detail['gross_weight'],
                        'net_weight' => $detail['net_weight'],
                        'unit_price' => $detail['unit_price'],
                        'initial_stock' => $detail['initial_stock'],
                        'available_stock' => $detail['initial_stock'],
                        'actual_stock' => $detail['initial_stock'],
                        'total_price' => $detail['total_price'],
                        'labor_cost' => $detail['labor_cost'],
                        'notes' => $detail['notes'] ?? '',
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
    public function destroy(IncomingItem $incomingItem)
    {
        $incomingItem->delete();

        return response()->json(['message' => 'Incoming Item deleted'], 204);
    }
}
