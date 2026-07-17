<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penggunaan extends Model
{
    protected $table = 'penggunaan'; // nama tabel eksplisit

    protected $fillable = [
        'node_id',
        'volume',
        'flowrate',
        'recorded_at',
    ];

    // relasi kebalikan: satu penggunaan MILIK satu hardware
public function node()
{
    return $this->belongsTo(Node::class);
}
}
