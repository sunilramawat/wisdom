<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUser extends Model
{
    protected $table = "event_users";
    protected $primaryKey = 'eu_id';

    public $timestamps = false;
}
