<?php

namespace App\Http\Controllers;

use App\Models\Penggunaan;
use Illuminate\Http\Request;

class PenggunaanController extends Controller
{
    // Menerima data pemakaian dari alat, lalu menyimpannya
    public function store(Request $request)
    {
        // (1) Validasi: pastikan data yang masuk lengkap & benar tipenya
        $data = $request->validate([
            'node_id' => 'required|exists:nodes,id',
            'volume'      => 'required|numeric',
            'flowrate'    => 'required|numeric',
            'recorded_at' => 'required|date',
        ]);

        // (2) Simpan ke tabel penggunaan
        $penggunaan = Penggunaan::create($data);

        // (3) Balas ke pengirim bahwa data berhasil disimpan
        return response()->json([
            'message' => 'Data pemakaian tersimpan',
            'data'    => $penggunaan,
        ], 201);
    }

    // Mengambil semua data pemakaian (yang terbaru di atas)
    public function index()
    {
        $data = Penggunaan::orderBy('recorded_at', 'desc')->get();

        return response()->json($data);
    }
}