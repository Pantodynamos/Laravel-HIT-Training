<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['code', 'name'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}

