<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Fuzzy\FuzzyService;

class EvaluationController extends Controller
{
    private FuzzyService $fuzzyService;

    public function __construct(FuzzyService $fuzzyService)
    {
        $this->fuzzyService = $fuzzyService;
    }

    public function evaluator(Request $request)
    {
        // Validasi struktur disesuaikan dengan skripsi Pratiwi (5 Input & 243 Rules Dinamis)
        $validated = $request->validate([
            'input' => 'required|array',
            'input.LCD' => 'required|numeric|between:0,100',
            'input.KondisiKeyboard' => 'required|numeric|between:0,100',
            'input.RAM' => 'required|numeric|min:0',
            'input.KesehatanBaterai' => 'required|numeric|between:0,100',
            'input.Processor' => 'required|numeric|min:0',
            'rules' => 'required|array',
            'rules.fuzzifikasi' => 'required|array',
            'rules.defuzzifikasi' => 'required|array',
            'rules.matrix_aturan' => 'required|array',
            'rules.matrix_aturan.*.lcd' => 'required|string|in:buruk,sedang,baik',
            'rules.matrix_aturan.*.keyboard' => 'required|string|in:buruk,sedang,baik',
            'rules.matrix_aturan.*.ram' => 'required|string|in:rendah,sedang,tinggi',
            'rules.matrix_aturan.*.baterai' => 'required|string|in:rendah,sedang,tinggi',
            'rules.matrix_aturan.*.processor' => 'required|string|in:rendah,sedang,tinggi',
            'rules.matrix_aturan.*.output' => 'required|string|in:tidak_layak,cukup_layak,layak',
            'rules.thresholds' => 'nullable|array',
            'rules.thresholds.tidak_layak_batas' => 'nullable|numeric|between:0,100',
            'rules.thresholds.layak_batas' => 'nullable|numeric|between:0,100',
        ]);

        $hasil = $this->fuzzyService->calculate($validated['input'], $validated['rules']);
        
        return response()->json([
            'status' => 'success',
            'data' => $hasil,
        ]);
    }
}