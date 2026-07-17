<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjadwalan extends Model
{
    protected $table = 'penjadwalan';   // nama tabel eksplisit (tanpa 's')

    protected $fillable = [
        'node_id',
        'hari',
        'jam_buka',
        'jam_tutup',
        'aktif',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
