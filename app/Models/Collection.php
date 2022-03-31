<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = "collections";

    public $timestamps = false;
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
        'category',
        'author',
        'status',
        'photo',
        'amazon_link',
        'ebay_link',
        'wordery',
        'other_link1',
        'other_link2',
    ];
}

