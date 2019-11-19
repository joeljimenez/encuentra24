<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarsVehicle extends Model
{
    protected $guarded = [];

    public function getBuildYearAttribute($attribute)
    {
        return date('Y', strtotime($attribute));
    }

}
