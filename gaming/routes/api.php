<?php

use App\Http\Controllers\CardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ScoreController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/scores', [ScoreController::class, 'store']);
Route::get('/scores/top10', [ScoreController::class, 'top10']);

Route::get('/cards/{suit}', [CardController::class, 'index']);
Route::post('/game/play', [GameController::class, 'play']);
