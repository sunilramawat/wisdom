<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Creater extends Model
{
    protected $table = "creaters";
    protected $primaryKey = 'c_id';

    public $timestamps = false;
}
