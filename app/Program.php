<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $primaryKey = 'program';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['program', 'counter'];
}

