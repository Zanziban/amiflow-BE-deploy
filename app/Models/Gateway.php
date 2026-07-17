<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gateway extends Model
{
    protected $fillable = ['kode_gateway', 'nama', 'aktif'];

public function nodes()
{
    return $this->hasMany(Node::class);
}
}
