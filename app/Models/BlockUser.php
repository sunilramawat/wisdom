<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockUser extends Model
{
    protected $table = "block_users";
     protected $primaryKey = 'b_id';

    public $timestamps = false;
}
