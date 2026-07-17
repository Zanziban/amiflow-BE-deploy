<?php

namespace App\Http\Controllers;

use App\Models\node;
use App\Models\Penggunaan;
use Illuminate\Http\Request;

class KlasifikasiController extends Controller
{
    // Konstanta standar jurnal
    const LITER_PER_ORANG_HARI = 60;   // liter/orang/hari
    const HARI_PER_BULAN       = 30;

    public function hitung($nodeId, $tahun, $bulan)
    {
        // (1) Ambil data node (untuk tahu jumlah penghuni)
        $node = Node::findOrFail($nodeId);
        $penghuni = $node->jumlah_penghuni;

        // (2) Ambil data pemakaian pada bulan & tahun tsb
        $data = Penggunaan::where('node_id', $nodeId)
            ->whereYear('recorded_at', $tahun)
            ->whereMonth('recorded_at', $bulan)
            ->get();

        if ($data->count() < 2) {
            return response()->json(['message' => 'Data belum cukup untuk bulan ini'], 400);
        }

        // (3) Konsumsi bulanan = volume tertinggi - terendah (karena kumulatif)
        $konsumsi = $data->max('volume') - $data->min('volume');

        // (4) Hitung batas dari jumlah penghuni
        $batasPerOrang = (self::LITER_PER_ORANG_HARI * self::HARI_PER_BULAN) / 1000; // 1.8 m3
        $batasHemat = $batasPerOrang * $penghuni;      // di bawah ini = Hemat
        $batasBoros = $batasHemat * 3;                 // di atas ini = Boros (mengikuti pola jurnal)

        // (5) Klasifikasi
        if ($konsumsi < $batasHemat) {
            $kategori = 'Hemat';
        } elseif ($konsumsi <= $batasBoros) {
            $kategori = 'Normal';
        } else {
            $kategori = 'Boros';
        }

        // (6) Kembalikan hasil lengkap
        return response()->json([
            'node'        => $node->nama_pemilik,
            'jumlah_penghuni' => $penghuni,
            'periode'         => "$bulan-$tahun",
            'konsumsi_m3'     => round($konsumsi, 2),
            'batas_hemat'     => round($batasHemat, 2),
            'batas_boros'     => round($batasBoros, 2),
            'kategori'        => $kategori,
        ]);
    }
}