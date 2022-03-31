<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SingleRoomMessage extends Model
{
    protected $table = "chat_room_msgs";
    protected $primaryKey = 'rm_id';

    public $timestamps = false;
}
