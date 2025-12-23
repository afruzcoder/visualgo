<?php

use App\Http\Controllers\DataNodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [DataNodeController::class, 'index']);

