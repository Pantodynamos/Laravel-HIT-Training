<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StockController;

Route::get('/', function () {
    return redirect()->route('stock.transaction.form');
});

Route::get('/stock/transaction', 'StockController@transactionForm')
    ->name('stock.transaction.form');

Route::post('/stock/transaction', 'StockController@handleTransaction')
    ->name('stock.transaction.submit');

Route::get('/stock/saldo', 'StockController@saldo')
    ->name('stock.saldo');

Route::get('/stock/saldo/data', 'StockController@saldoData')
    ->name('stock.saldo.data');

Route::get('/stock/history', 'StockController@history')
    ->name('stock.history');

Route::get('/stock/history/data', 'StockController@historyData')
    ->name('stock.history.data');
