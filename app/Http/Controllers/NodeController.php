<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Penggunaan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function index($gatewayId)
    {
        $data = Node::with('controlValve')
            ->where('gateway_id', $gatewayId)   // <-- hanya node gateway ini
            ->get()
            ->map(function ($node) {
                $readings = Penggunaan::where('node_id', $node->id)
                    ->orderBy('recorded_at')->get();

                $usageHarian = 0;
                $peak = 0;
                if ($readings->count() >= 2) {
                    $total = $readings->max('volume') - $readings->min('volume');
                    $mulai = Carbon::parse($readings->first()->recorded_at);
                    $akhir = Carbon::parse($readings->last()->recorded_at);
                    $hari = max($mulai->diffInDays($akhir), 1);
                    $usageHarian = $total / $hari;
                    $peak = $readings->max('flowrate');
                }

                $valveOpen = optional($node->controlValve)->status === 'active';

                return [
                    'id' => (string) $node->id,
                    'code' => $node->kode_node,
                    'online' => (bool) $node->online,
                    'owner' => $node->nama_pemilik,
                    'totalUsers' => $node->jumlah_penghuni,
                    'waterUsageM3' => round($usageHarian, 2),
                    'peakFlow' => round($peak, 2),
                    'valveOpen' => $valveOpen,
                ];
            });

        return response()->json($data);
    }
    public function destroy(string $id)
    {
        $node = Node::find($id);

        if (!$node) {
            return response()->json(['message' => 'Node tidak ditemukan'], 404);
        }

        $node->delete(); // penggunaan, penjadwalan, control_valve ikut terhapus (cascade)

        return response()->json(['message' => 'Node dihapus']);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'gateway_id' => 'required|exists:gateways,id',
            'kode_node' => 'required|string|unique:nodes,kode_node',
            'nama_pemilik' => 'required|string',
            'jumlah_penghuni' => 'required|integer|min:1',
            'password' => 'required|string|min:4',
        ]);

        $node = Node::create([
            'gateway_id' => $data['gateway_id'],
            'kode_node' => $data['kode_node'],
            'nama_pemilik' => $data['nama_pemilik'],
            'jumlah_penghuni' => $data['jumlah_penghuni'],
            'password' => bcrypt($data['password']), // simpan ter-hash
            'aktif' => true,
            'online' => true, // node baru: online setelah alat mengirim data
        ]);

        // kembalikan dalam bentuk yang sama dengan index (cocok dengan entitas Node Flutter)
        return response()->json([
            'id' => (string) $node->id,
            'code' => $node->kode_node,
            'online' => (bool) $node->online,
            'owner' => $node->nama_pemilik,
            'totalUsers' => $node->jumlah_penghuni,
            'waterUsageM3' => 0,
            'peakFlow' => 0,
            'valveOpen' => false,
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_pemilik' => 'required|string|max:255',
            'jumlah_penghuni' => 'required|integer|min:1',
        ]);

        $node = Node::findOrFail($id);

        $node->nama_pemilik = $validated['nama_pemilik'];
        $node->jumlah_penghuni = $validated['jumlah_penghuni'];

        $node->save();

        return response()->json([
            'id' => (string) $node->id,
            'code' => $node->kode_node,
            'online' => (bool) $node->online,
            'owner' => $node->nama_pemilik,
            'totalUsers' => $node->jumlah_penghuni,
            'waterUsageM3' => 0,
            'peakFlow' => 0,
            'valveOpen' => optional($node->controlValve)->status === 'active',
        ]);
    }
}