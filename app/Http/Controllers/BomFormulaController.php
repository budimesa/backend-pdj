<?php

namespace App\Http\Controllers;

use App\Models\BomFormula;
use App\Models\BomFormulaDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BomFormulaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = BomFormula::query();

        // Apply global filter
        if ($request->has('filters.global.value')) {
            $query->where('item_code', 'like', '%' . $request->input('filters.global.value') . '%')
                ->orWhere('formula', 'like', '%' . $request->input('filters.global.value') . '%');
        }

        // Apply specific filters
        if ($request->has('filters.item_code.value')) {
            $query->where('item_code', 'like', $request->input('filters.item_code.value') . '%');
        }

        if ($request->has('filters.formula.value')) {
            $query->where('formula', 'like', $request->input('filters.formula.value') . '%');
        }

        $query->orderBy('created_at', 'desc'); 
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $results = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $results->items(),
            'total' => $results->total(),
            'per_page' => $results->perPage(),
            'current_page' => $results->currentPage(),
            'from' => $results->firstItem(),
            'last_page' => $results->lastPage(),
            'offset' => $offset
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'formula' => 'required|unique:mst_bom_formula,formula',
            'item_code' => 'required',
            'use_priority' => 'required|string|max:1',
            'details' => 'required|array',
            'details.*.item_code' => 'required|string',
            'details.*.qty_std_use' => 'required|numeric',
            // 'details.*.spar_rate' => 'required|numeric',
            'details.*.unit_use' => 'required|numeric',
            'details.*.expiry_date' => 'required|date',
            'details.*.effective_date' => 'required|date',
            'details.*.usage_percentage' => 'required|numeric',
            'details.*.main_material' => 'required|string',
        ]);

        // Mulai transaction
        DB::transaction(function () use ($request) {
            // Insert ke tabel mst_bom_formula
            $bomFormula = BomFormula::create([
                'formula' => $request->formula,
                'item_code' => $request->item_code,
                'use_priority' => $request->use_priority,
                'created_by' => Auth::id(),
            ]);

            // Insert ke tabel mst_bom_formula_details
            foreach ($request->details as $detail) {
                BomFormulaDetail::create([
                    'mst_bom_formula_id' => $bomFormula->id,
                    'formula' => $bomFormula->formula,
                    'item_code' => $detail['item_code'],
                    'qty_std_use' => $detail['qty_std_use'],
                    'spar_rate' => $detail['spar_rate'],
                    'unit_use' => $detail['unit_use'],
                    'loss_rate' => $detail['loss_rate'],
                    'expiry_date' => $detail['expiry_date'],
                    'effective_date' => $detail['effective_date'],
                    'usage_percentage' => $detail['usage_percentage'],
                    'main_material' => $detail['main_material'],
                    'created_by' => Auth::id(),
                ]);
            }
        });

        return response()->json(['message' => 'Data inserted successfully'], 201);
    }

    public function show(BomFormula $bomFormula)
    {
        // Ambil item_code dari BomFormula
        $itemCode = $bomFormula->item_code;
        
        // Cek data di mst_wip_finished_goods
        $result = DB::table('mst_bom_formula')
            ->join('mst_wip_finished_goods', 'mst_bom_formula.item_code', '=', 'mst_wip_finished_goods.item_code')
            ->leftJoin('mst_bom_formula_details', 'mst_bom_formula.id', '=', 'mst_bom_formula_details.mst_bom_formula_id')
            ->select(
                'mst_bom_formula.*',
                'mst_wip_finished_goods.unit_usg',
                'mst_wip_finished_goods.item_spec',
                'mst_wip_finished_goods.item_name',
                // Menambahkan kolom dari mst_bom_formula_details ke dalam response
                'mst_bom_formula_details.formula',
                'mst_bom_formula_details.item_code as detail_item_code',
                'mst_bom_formula_details.qty_std_use',
                'mst_bom_formula_details.spar_rate',
                'mst_bom_formula_details.unit_use',
                'mst_bom_formula_details.loss_rate',
                'mst_bom_formula_details.expiry_date',
                'mst_bom_formula_details.effective_date',
                'mst_bom_formula_details.usage_percentage',
                'mst_bom_formula_details.main_material'
            )
            ->where('mst_bom_formula.item_code', $itemCode)
            ->first(); // Mengambil satu hasil saja

        // Jika tidak ditemukan di mst_wip_finished_goods, cek di mst_semi_finished_goods
        if (!$result) {
            $result = DB::table('mst_bom_formula')
                ->join('mst_semi_finished_goods', 'mst_bom_formula.item_code', '=', 'mst_semi_finished_goods.item_code')
                ->leftJoin('mst_bom_formula_details', 'mst_bom_formula.id', '=', 'mst_bom_formula_details.mst_bom_formula_id')
                ->select(
                    'mst_bom_formula.*',
                    'mst_semi_finished_goods.unit_usg',
                    'mst_semi_finished_goods.item_spec',
                    'mst_semi_finished_goods.item_name',
                    // Menambahkan kolom dari mst_bom_formula_details ke dalam response
                    'mst_bom_formula_details.formula',
                    'mst_bom_formula_details.item_code as detail_item_code',
                    'mst_bom_formula_details.qty_std_use',
                    'mst_bom_formula_details.spar_rate',
                    'mst_bom_formula_details.unit_use',
                    'mst_bom_formula_details.loss_rate',
                    'mst_bom_formula_details.expiry_date',
                    'mst_bom_formula_details.effective_date',
                    'mst_bom_formula_details.usage_percentage',
                    'mst_bom_formula_details.main_material'
                )
                ->where('mst_bom_formula.item_code', $itemCode)
                ->first(); // Mengambil satu hasil saja
        }

        // Jika ada hasil, return sebagai response JSON
        if ($result) {
            // Menyusun hasil 'details' jika ada data dalam mst_bom_formula_details
            $details = DB::table('mst_bom_formula_details')
                ->where('mst_bom_formula_details.mst_bom_formula_id', $result->id)
                ->get();

            // Menyusun response JSON dengan data detail
            return response()->json([
                'bom_formula' => $result,
                'details' => $details
            ]);
        } else {
            // Jika tidak ditemukan, return error
            return response()->json(['message' => 'Item code not found in BOM Formula or associated tables'], 404);
        }
    }

    public function update(Request $request, BomFormula $bomFormula)
    {
        // Validasi data input
        $data = $request->validate([
            'formula' => 'required|unique:mst_bom_formula,formula,' . $bomFormula->id,
            'item_code' => 'required',
            'use_priority' => 'nullable|string|max:1',
            'details' => 'required|array',
            'details.*.item_code' => 'required|string',
            'details.*.qty_std_use' => 'required|numeric',
            // 'details.*.spar_rate' => 'required|numeric',
            'details.*.unit_use' => 'required|numeric',
            'details.*.expiry_date' => 'required|date',
            'details.*.effective_date' => 'required|date',
            'details.*.usage_percentage' => 'required|numeric',
            'details.*.main_material' => 'required|string',
        ]);

        // Menambahkan informasi siapa yang melakukan update
        $data['updated_by'] = Auth::id();

        // Mulai transaction untuk update data
        DB::transaction(function () use ($request, $bomFormula) {
            // Update ke tabel mst_bom_formula
            $bomFormula->update([
                'formula' => $request->formula,
                'item_code' => $request->item_code,
                'use_priority' => $request->use_priority ?? $bomFormula->use_priority,
                'updated_by' => Auth::id(),
            ]);

            // Hapus detail yang sudah tidak ada di request
            BomFormulaDetail::where('mst_bom_formula_id', $bomFormula->id)
                ->whereNotIn('item_code', array_column($request->details, 'item_code'))
                ->delete();

            // Insert atau update ke tabel mst_bom_formula_details
            foreach ($request->details as $detail) {
                // Cek apakah detail sudah ada berdasarkan item_code
                $existingDetail = BomFormulaDetail::where('mst_bom_formula_id', $bomFormula->id)
                    ->where('item_code', $detail['item_code'])
                    ->first();

                if ($existingDetail) {
                    // Update jika detail sudah ada
                    $existingDetail->update([
                        'qty_std_use' => $detail['qty_std_use'],
                        'spar_rate' => $detail['spar_rate'],
                        'unit_use' => $detail['unit_use'],
                        'loss_rate' => $detail['loss_rate'],
                        'expiry_date' => $detail['expiry_date'],
                        'effective_date' => $detail['effective_date'],
                        'usage_percentage' => $detail['usage_percentage'],
                        'main_material' => $detail['main_material'],
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Insert jika detail baru
                    BomFormulaDetail::create([
                        'mst_bom_formula_id' => $bomFormula->id,
                        'formula' => $bomFormula->formula,
                        'item_code' => $detail['item_code'],
                        'qty_std_use' => $detail['qty_std_use'],
                        'spar_rate' => $detail['spar_rate'],
                        'unit_use' => $detail['unit_use'],
                        'loss_rate' => $detail['loss_rate'],
                        'expiry_date' => $detail['expiry_date'],
                        'effective_date' => $detail['effective_date'],
                        'usage_percentage' => $detail['usage_percentage'],
                        'main_material' => $detail['main_material'],
                        'created_by' => Auth::id(),
                    ]);
                }
            }
        });

        return response()->json(['message' => 'BOM Formula updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BomFormula $bomFormula)
    {
        $bomFormula->delete();
        return response()->json(['message' => 'Formula deleted'], 204);
    }
}
