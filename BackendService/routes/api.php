<?php

use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\ProcessorController;
use App\Models\AiRelevanceKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ai/status', fn () => response()->json([
    'available' => (bool) config('services.gemini.enabled') && filled(config('services.gemini.key')),
]));

Route::get('/ai/keywords', fn () => response()->json([
    'data' => AiRelevanceKeyword::orderBy('keyword')->pluck('keyword'),
]));

Route::post('/ai/keywords', function (Request $request) {
    $validated = $request->validate(['keyword' => 'required|string|max:80']);
    $keyword = trim(mb_strtolower($validated['keyword']));
    $item = AiRelevanceKeyword::firstOrCreate(['keyword' => $keyword]);

    return response()->json(['data' => $item], $item->wasRecentlyCreated ? 201 : 200);
});

Route::delete('/ai/keywords/{keyword}', function (string $keyword) {
    AiRelevanceKeyword::where('keyword', $keyword)->delete();
    return response()->noContent();
});

Route::get('/processors', [ProcessorController::class, 'index']);
Route::post('/processors', [ProcessorController::class, 'store']);
Route::delete('/processors/{processor}', [ProcessorController::class, 'destroy']);

Route::get('/assessments', [AssessmentController::class, 'index']);
Route::post('/assessments', [AssessmentController::class, 'store']);
Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy']);
