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
    
    /**
     * Get the latest non regular batch
     *
     * @return \App\Models\Batch
     */

    public function generateBatchCode($supplier_id) {
        $supplier = Supplier::find($supplier_id);
    
        if (!$supplier) {
            return response()->json(['message' => 'Supplier not found'], 404);
        }
    
        $year = date('y');
        $monthLetter = $this->getMonthLetter(date('n'));
        $batchCodePrefix = '';
    
        if ($supplier->supplier_type === 'non-regular') {
            $batchCodePrefix = 'KPK';
            $latestBatch = Batch::where('batch_code_type', 'non-regular')
                                ->orderBy('id', 'desc')
                                ->first();
        } else {
            $batchCodePrefix = substr($supplier->supplier_code, 0, 3);
            $latestBatch = Batch::where('supplier_id', $supplier_id)
                                ->where('batch_code_type', 'regular')
                                ->orderBy('id', 'desc')
                                ->first();
        }
    
        $counter = 1;
    
        if ($latestBatch) {
            preg_match('/(\d{4})([A-Z])/', $latestBatch->batch_code, $matches);
            $lastBatchMonthLetter = isset($matches[2]) ? $matches[2] : '';
    
            if ($lastBatchMonthLetter !== $monthLetter) {
                $counter = 1;
            } else {
                $counter = isset($matches[1]) ? intval($matches[1]) + 1 : $counter;
            }
        }
    
        // Cek dan pastikan batch code unik
        do {
            $batchCode = sprintf('%s%s%s%04d', $batchCodePrefix, $year, $monthLetter, $counter);
            $exists = Batch::where('batch_code', $batchCode)->exists();
            if ($exists) {
                $counter++;
            }
        } while ($exists);
    
        return $batchCode;
    }
    
    private function getMonthLetter($month) {
        $months = [
            '1' => 'A', '2' => 'B', '3' => 'C', '4' => 'D', 
            '5' => 'E', '6' => 'F', '7' => 'G', '8' => 'H', 
            '9' => 'I', '10' => 'J', '11' => 'K', '12' => 'L'
        ];
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
