<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    protected $table = "events";

    public $timestamps = false;
  
    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'e_id',
        'e_u_id',
        'e_channel',
        'e_token',
        'e_status',
    ];




    
}
