<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = ['number', 'name'];

    public function logs()
    {
        return $this->hasMany('\App\StationsLog');
    }
}
