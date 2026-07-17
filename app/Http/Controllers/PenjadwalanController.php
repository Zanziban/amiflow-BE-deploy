<?php

namespace App\Http\Controllers;

use App\Models\Penjadwalan;
use Illuminate\Http\Request;

class PenjadwalanController extends Controller
{
    // Simpan / perbarui jadwal satu hari untuk sebuah node
    public function store(Request $request)
    {
        // (1) Validasi — di sinilah aturan jam tutup maksimal 23:59
        $data = $request->validate([
            'node_id'   => 'required|exists:nodes,id',
            'hari'      => 'required|string',
            'jam_buka'  => 'nullable|date_format:H:i',
            'jam_tutup' => 'nullable|date_format:H:i|before_or_equal:23:59', // (2)
            'aktif'     => 'required|boolean',
        ]);

        // (3) updateOrCreate: kalau jadwal hari itu sudah ada -> perbarui; kalau belum -> buat
        $jadwal = Penjadwalan::updateOrCreate(
            ['node_id' => $data['node_id'], 'hari' => $data['hari']],
            $data
        );

        return response()->json([
            'message' => 'Jadwal tersimpan',
            'data'    => $jadwal,
        ], 201);
    }

    // Ambil semua jadwal milik satu node
    public function index($nodeId)
    {
        $jadwal = Penjadwalan::where('node_id', $nodeId)->get();

        return response()->json($jadwal);
    }
}