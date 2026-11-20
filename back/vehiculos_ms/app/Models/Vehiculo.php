<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';
    protected $hidden = ['created_at', 'updated_at'];
}
