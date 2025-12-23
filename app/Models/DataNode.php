<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataNode extends Model
{
    protected $fillable = [
        'name', 'value', 'type', 'description', 'meta_data'
    ];

    protected $casts = [
        'meta_data' => 'array'
    ];
}
