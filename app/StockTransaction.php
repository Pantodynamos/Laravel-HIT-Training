<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = [
        'reference','transaction_date','quantity','stock_id','program_id'
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program');
    }
}
