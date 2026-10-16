<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// These routes mirror /backend/main.py exactly, so the same web/ frontend or
// Flutter app can talk to either backend without any client-side changes.

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/models', [ModelController::class, 'index']);
Route::get('/models/{id}', [ModelController::class, 'show']);
Route::post('/models/upload', [ModelController::class, 'upload']);
Route::delete('/models/{id}', [ModelController::class, 'destroy']);

Route::post('/models/{modelId}/questions', [QuizController::class, 'addQuestion']);
Route::get('/models/{modelId}/questions', [QuizController::class, 'questionsForModel']);
Route::post('/questions/{questionId}/check', [QuizController::class, 'checkAnswer']);
Route::post('/quiz/submit', [QuizController::class, 'submitResult']);

Route::post('/sessions', [ProgressController::class, 'logSession']);
Route::get('/students/{id}/progress', [ProgressController::class, 'studentProgress']);

Route::get('/health', fn () => response()->json(['status' => 'ok', 'time' => now()]));
