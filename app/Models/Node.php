<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $fillable = [
    'gateway_id',
    'kode_node',
    'nama_pemilik',
    'jumlah_penghuni',
    'password',
    'aktif',
    'online',
];

protected $hidden = ['password'];

// Node ini MILIK satu gateway
public function gateway()
{
    return $this->belongsTo(Gateway::class);
}

// Node punya banyak data penggunaan (aktif penuh setelah Fase 2)
public function penggunaan()
{
    return $this->hasMany(Penggunaan::class);
}

// Node punya satu control valve (aktif penuh setelah Fase 2)
public function controlValve()
{
    return $this->hasOne(ControlValve::class);
}
public function penjadwalan()
{
    return $this->hasMany(Penjadwalan::class);
}
}
