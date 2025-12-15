<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/stock/transaction');
});

Route::get('/stock/transaction', [StockController::class, 'transactionForm'])
    ->name('stock.transaction.form');

Route::post('/stock/transaction', [StockController::class, 'handleTransaction'])
    ->name('stock.transaction.submit');

Route::get('/stock/saldo', [StockController::class, 'saldo'])
    ->name('stock.saldo');

Route::get('/stock/history', [StockController::class, 'history'])
    ->name('stock.history');
