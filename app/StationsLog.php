<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StationsLog extends Model
{
    public function station()
    {
        return $this->belongsTo('\App\Station');
    }
}
