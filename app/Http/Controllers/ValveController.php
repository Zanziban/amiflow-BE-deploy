<?php

namespace App\Http\Controllers;

use App\Models\ControlValve;
use App\Models\Node;
use Illuminate\Http\Request;

class ValveController extends Controller
{
    public function update(Request $request, string $nodeId)
    {
        $data = $request->validate([
            'open' => 'required|boolean', // true = buka (active), false = tutup (inactive)
        ]);

        // pastikan node-nya ada
        $node = Node::find($nodeId);
        if (!$node) {
            return response()->json(['message' => 'Node tidak ditemukan'], 404);
        }

        $status = $data['open'] ? 'active' : 'inactive';

        // updateOrCreate: kalau valve node ini sudah ada -> perbarui; kalau belum -> buat
        $valve = ControlValve::updateOrCreate(
            ['node_id' => $nodeId],
            ['status' => $status, 'diubah_pada' => now()],
        );

        return response()->json([
            'message'   => 'Status valve diperbarui',
            'valveOpen' => $valve->status === 'active',
        ]);
    }
}