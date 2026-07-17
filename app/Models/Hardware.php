<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hardware extends Model
{
    protected $table = 'hardware'; // (1)

    protected $fillable = [        // (2)
        'kode_perangkat',
        'nama_pemilik',
        'jumlah_penghuni',
        'password',
        'aktif',
    ];

    protected $hidden = ['password']; // (3)

    // (4) satu hardware punya BANYAK penggunaan
    public function penggunaan()
    {
        return $this->hasMany(Penggunaan::class);
    }

    // (5) satu hardware punya SATU control valve
    public function controlValve()
    {
        return $this->hasOne(ControlValve::class);
    }
}
