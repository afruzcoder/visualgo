<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataNodeClosure extends Model
{
    protected $table = 'data_node_closure';
    public $timestamps = false;

    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth'
    ];
}
