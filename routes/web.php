<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('/transactions', TransactionController::class);
