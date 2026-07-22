<?php

namespace App\Http\Controllers;

use App\Models\ControlValve;
use App\Models\Node;
use App\Models\Penggunaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KlasifikasiController extends Controller
{
    // Ambang batas per orang per bulan, satuan meter kubik (m3).
    // Gap antar batas sengaja 0,01 m3 (=10 liter) sebagai zona toleransi:
    // - <= HEMAT_MAX_PER_ORANG           -> Hemat
    // - >  HEMAT_MAX_PER_ORANG dan
    //   <  BOROS_MIN_PER_ORANG           -> Normal (mencakup rentang tipikal 1,80-1,81)
    // - >= BOROS_MIN_PER_ORANG           -> Boros
    const HEMAT_MAX_PER_ORANG = 1.79;
    const BOROS_MIN_PER_ORANG = 1.82;

    // ML baru boleh mengklasifikasi setelah data mencakup satu bulan penuh (~30 hari kalender berbeda),
    // dengan tetap menyimpan histori harian dari hari-hari sebelumnya.
    //const MINIMAL_HARI_DATA = 30;

    const MINIMAL_CAKUPAN_DATA = 0.90;

    public function hitung($nodeId, $tahun, $bulan)
    {
        // (1) Ambil data node (untuk tahu jumlah penghuni)
        $node = Node::findOrFail($nodeId);
        $penghuni = $node->jumlah_penghuni;

        // (2) Ambil histori pemakaian pada bulan & tahun tsb, urut berdasarkan waktu
        $data = Penggunaan::where('node_id', $nodeId)
            ->whereYear('recorded_at', $tahun)
            ->whereMonth('recorded_at', $bulan)
            ->orderBy('recorded_at')
            ->get();

        if ($data->count() < 2) {
            return response()->json([
                'message' => 'Data belum cukup untuk bulan ini',
                'kategori' => null,
            ], 400);
        }

        // (3) ML hanya aktif setelah data mencakup 30 hari kalender berbeda.
        // Dihitung dari jumlah tanggal unik yang punya data, bukan selisih waktu
        // mentah antara sampel pertama & terakhir -- karena sensor jarang mengirim
        // pembacaan pertamanya tepat jam 00:00:00, sehingga selisih waktu literal
        // hampir selalu sedikit di bawah 30 hari meski tanggal kalendernya sudah genap 30.
        $jumlahHariUnik = $data->pluck('recorded_at')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->unique()
            ->count();

        $jumlahHariDalamBulan = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        $cakupanData = $jumlahHariUnik / $jumlahHariDalamBulan;

        if ($cakupanData < self::MINIMAL_CAKUPAN_DATA) {
            return response()->json([
                'message' => 'ML belum aktif, data hanya mencakup '
                    . $jumlahHariUnik . ' dari '
                    . $jumlahHariDalamBulan . ' hari dalam bulan ini',
                'hari_terekam' => $jumlahHariUnik,
                'hari_dalam_bulan' => $jumlahHariDalamBulan,
                'cakupan_data' => round($cakupanData * 100, 2),
                'kategori' => null,
            ], 202);
        }

        // (4) Konsumsi bulanan = volume tertinggi - terendah (karena kumulatif),
        // dikonversi ke m3 jika volume disimpan dalam liter.
        $konsumsiLiter = $data->max('volume') - $data->min('volume');
        $konsumsi = $konsumsiLiter / 1000; // m3

        // (5) Hitung batas dari jumlah penghuni
        $batasHemat = self::HEMAT_MAX_PER_ORANG * $penghuni;
        $batasBoros = self::BOROS_MIN_PER_ORANG * $penghuni;

        // (6) Klasifikasi
        if ($konsumsi <= $batasHemat) {
            $kategori = 'Hemat';
        } elseif ($konsumsi >= $batasBoros) {
            $kategori = 'Boros';
        } else {
            $kategori = 'Normal';
        }

        // (7) Setiap kategori memicu notifikasi ke pemilik node
        $this->kirimNotifikasi($node, $kategori, $konsumsi);

        // (8) Kategori Boros otomatis memicu aksi pencegahan: matikan relay/valve
        $relayDimatikan = false;
        if ($kategori === 'Boros') {
            ControlValve::updateOrCreate(
                ['node_id' => $nodeId],
                ['status' => 'inactive', 'diubah_pada' => now()],
            );
            $relayDimatikan = true;
        }

        // (9) Kembalikan hasil lengkap
        return response()->json([
            'node'            => $node->nama_pemilik,
            'jumlah_penghuni' => $penghuni,
            'periode'         => "$bulan-$tahun",
            'hari_terekam'    => $jumlahHariUnik,
            'konsumsi_m3'     => round($konsumsi, 3),
            'batas_hemat'     => round($batasHemat, 3),
            'batas_boros'     => round($batasBoros, 3),
            'kategori'        => $kategori,
            'relay_dimatikan' => $relayDimatikan,
        ]);
    }

    /**
     * Kirim notifikasi ke pemilik node sesuai kategori klasifikasi.
     * Saat ini masih berupa log + payload di response; tinggal diganti
     * dengan channel notifikasi asli (mis. FCM/database notification)
     * begitu tabel/relasi notifikasi sudah tersedia.
     */
    private function kirimNotifikasi(Node $node, string $kategori, float $konsumsi): void
    {
        $pesan = match ($kategori) {
            'Hemat'  => 'Pemakaian air Anda bulan ini tergolong hemat.',
            'Normal' => 'Pemakaian air Anda bulan ini normal.',
            'Boros'  => 'Pemakaian air Anda bulan ini boros, relay otomatis dimatikan.',
            default  => 'Status pemakaian air Anda telah diperbarui.',
        };

        Log::info('Notifikasi klasifikasi konsumsi', [
            'node_id'   => $node->id,
            'kategori'  => $kategori,
            'konsumsi'  => $konsumsi,
            'pesan'     => $pesan,
        ]);
    }
}
