<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SummaryDaily extends Model
{
    protected $table = 'summary_daily';
    protected $fillable = ['day', 'stations', 'bicycles', 'utilization'];
}
