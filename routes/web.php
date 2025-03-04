<?php

use App\Http\Controllers\socialController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('auth/callback', [socialController::class, 'handleAuthCallback']);
