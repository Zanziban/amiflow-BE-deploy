<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use Illuminate\Http\Request; 

class GatewayController extends Controller
{
    public function index()
    {
        $data = Gateway::all()->map(function ($gw) {
            return [
                'id'          => (string) $gw->id,
                'name'        => $gw->nama,
                'gatewayCode' => $gw->kode_gateway,
                'isOnline'    => (bool) $gw->aktif,
            ];
        });

        return response()->json($data);
    }
    public function destroy($id)
    {
        $gateway = Gateway::find($id);

        if (!$gateway) {
            return response()->json(['message' => 'Gateway tidak ditemukan'], 404);
        }

        $gateway->delete(); // node & data terkait ikut terhapus (cascade)

        return response()->json(['message' => 'Gateway dihapus']);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode_gateway' => 'required|string|unique:gateways,kode_gateway',
            'nama'         => 'required|string',
        ]);

        $gateway = Gateway::create([
            'kode_gateway' => $data['kode_gateway'],
            'nama'         => $data['nama'],
            'aktif'        => true, // gateway baru dianggap aktif
        ]);

        // kembalikan dalam bentuk yang sama dengan index (cocok dengan Flutter)
        return response()->json([
            'id'          => (string) $gateway->id,
            'name'        => $gateway->nama,
            'gatewayCode' => $gateway->kode_gateway,
            'isOnline'    => (bool) $gateway->aktif,
        ], 201);
    }
}