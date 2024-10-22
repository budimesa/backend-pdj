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
