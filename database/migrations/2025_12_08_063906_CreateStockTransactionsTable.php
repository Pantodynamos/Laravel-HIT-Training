<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference', 30);
            $table->date('transaction_date');
            $table->integer('quantity');
            $table->integer('stock_id');
            $table->unsignedBigInteger('program_id');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->foreign('program_id')->references('id')->on('programs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_transactions');
    }
}
