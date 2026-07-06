<?php

use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\ProcessorController;
use Illuminate\Support\Facades\Route;

Route::get('/processors', [ProcessorController::class, 'index']);
Route::post('/processors', [ProcessorController::class, 'store']);
Route::delete('/processors/{processor}', [ProcessorController::class, 'destroy']);

Route::get('/assessments', [AssessmentController::class, 'index']);
Route::post('/assessments', [AssessmentController::class, 'store']);
Route::get('/assessments/{id}', [AssessmentController::class, 'show']);
Route::delete('/assessments/{id}', [AssessmentController::class, 'destroy']);
