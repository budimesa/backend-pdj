<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;
use App\Models\Supplier;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Batch::orderBy('id', 'desc')->get();
    }

    public function fetchLatestRegularBatch($supplier_id) {
        return Batch::orderBy('id', 'desc')->where('supplier_id', $supplier_id)->where('batch_code_type', 'regular')->first();
    }


    /**
     * Get the latest non regular batch
     *
     * @return \App\Models\Batch
     */
    public function getNonRegularBatch() {
        // return Batch::all();
        return Batch::orderBy('id', 'desc')->where('batch_code_type', 'non-regular')->first();
    }

    public function generateBatchCode($supplier_id) {
        // Ambil supplier berdasarkan ID
        $supplier = Supplier::find($supplier_id);

        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }

        $year = date('y'); // Ambil tahun dua digit
        $monthLetter = $this->getMonthLetter(date('n')); // Ambil huruf untuk bulan saat ini
        $batchCodePrefix = '';

        if ($supplier->supplier_type === 'non-regular') {
            $batchCodePrefix = 'KPK';
            
            // Ambil counter terakhir untuk non-regular tanpa memeriksa supplier_id
            $latestBatch = Batch::where('batch_code_type', 'non-regular')
                                ->orderBy('id', 'desc')
                                ->first();
        } else {
            $batchCodePrefix = substr($supplier->supplier_code, 0, 3);
            // Ambil counter terakhir untuk regular berdasarkan supplier_id
            $latestBatch = Batch::where('supplier_id', $supplier_id)
                                ->where('batch_code_type', 'regular')
                                ->orderBy('id', 'desc')
                                ->first();
        }

        $counter = 1; // Default counter
        if ($latestBatch) {
            // Ambil counter dari batch terakhir
            preg_match('/(\d+)$/', $latestBatch->batch_code, $matches);
            $counter = isset($matches[1]) ? intval($matches[1]) + 1 : $counter;
        }

        // Format batch_code
        $batchCode = sprintf('%s %s%s %04d', $batchCodePrefix, $year, $monthLetter, $counter);
        
        return $batchCode;
    }
    
    private function getMonthLetter($month) {
        $months = ['1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', '5' => 'E', '6' => 'F', 
                   '7' => 'G', '8' => 'H', '9' => 'I', '10' => 'J', '11' => 'K', '12' => 'L'];
        return isset($months[$month]) ? $months[$month] : '';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'batch_code_type' => 'required|in:regular,non-regular',
        ]);
    
        // Mengenerate batch_code
        $batchCode = $this->generateBatchCode($request->supplier_id);
    
        // Simpan batch baru
        $batch = Batch::create([
            'batch_code' => $batchCode,
            'supplier_id' => $request->supplier_id,
            'batch_code_type' => $request->batch_code_type,
        ]);

        $batch = Batch::create($validated);

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
