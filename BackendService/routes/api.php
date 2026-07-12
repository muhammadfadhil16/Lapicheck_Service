<?php

use App\Http\Controllers\Api\AiSettingController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\ProcessorController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/status', [AiSettingController::class, 'status']);
Route::get('/ai/models', [AiSettingController::class, 'models']);
Route::put('/ai/model', [AiSettingController::class, 'updateModel']);
Route::post('/ai/test-connection', [AiSettingController::class, 'testConnection']);
Route::get('/ai/keywords', [AiSettingController::class, 'keywords']);
Route::post('/ai/keywords', [AiSettingController::class, 'storeKeyword']);
Route::delete('/ai/keywords/{keyword}', [AiSettingController::class, 'destroyKeyword']);

Route::get('/processors', [ProcessorController::class, 'index']);
Route::post('/processors', [ProcessorController::class, 'store']);
Route::delete('/processors/{processor}', [ProcessorController::class, 'destroy']);

Route::get('/assessments', [AssessmentController::class, 'index']);
Route::post('/assessments', [AssessmentController::class, 'store']);
Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy']);
