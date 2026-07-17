<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlValve extends Model
{
    protected $fillable = [
        'node_id',
        'status',
        'diubah_pada',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
