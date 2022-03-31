<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    public $timestamps = false;
    protected $table = "partners";
	/**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'title',
        'desc',
        'type',
        'status',
        'photo',
    ];
    
}
