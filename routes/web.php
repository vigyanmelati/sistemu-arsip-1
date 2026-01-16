<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArsipController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/arsip', [ArsipController::class, 'index']);
