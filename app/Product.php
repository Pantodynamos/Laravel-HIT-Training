<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['code', 'name'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}

