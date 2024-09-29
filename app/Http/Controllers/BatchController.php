<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required',
            'batch_code' => 'required|unique:batches,batch_code',
            'supplier_id' => 'required',
            'batch_code_type' => 'required|in:regular,non-regular',
        ]);

        $validated['created_by'] = Auth::id();

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
