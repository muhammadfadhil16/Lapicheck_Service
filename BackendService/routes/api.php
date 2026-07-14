<?php

use App\Http\Controllers\Api\AiSettingController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\LaptopController;
use Illuminate\Support\Facades\Route;

Route::get('/ai/status', [AiSettingController::class, 'status']);
Route::get('/ai/models', [AiSettingController::class, 'models']);
Route::put('/ai/model', [AiSettingController::class, 'updateModel']);
Route::post('/ai/test-connection', [AiSettingController::class, 'testConnection']);
Route::get('/ai/keywords', [AiSettingController::class, 'keywords']);
Route::post('/ai/keywords', [AiSettingController::class, 'storeKeyword']);
Route::delete('/ai/keywords/{keyword}', [AiSettingController::class, 'destroyKeyword']);

Route::get('/laptop-brands', [LaptopController::class, 'brands']);
Route::post('/laptop-brands', [LaptopController::class, 'storeBrand']);
Route::put('/laptop-brands/{brand}', [LaptopController::class, 'updateBrand']);
Route::delete('/laptop-brands/{brand}', [LaptopController::class, 'destroyBrand']);
Route::get('/laptops', [LaptopController::class, 'index']);
Route::post('/laptops', [LaptopController::class, 'store']);
Route::put('/laptops/{laptop}', [LaptopController::class, 'update']);
Route::delete('/laptops/{laptop}', [LaptopController::class, 'destroy']);

Route::get('/assessments', [AssessmentController::class, 'index']);
Route::post('/assessments', [AssessmentController::class, 'store']);
Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy']);
