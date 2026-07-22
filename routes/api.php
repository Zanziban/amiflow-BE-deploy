<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaanController;
use App\Http\Controllers\KlasifikasiController;
use App\Http\Controllers\PenjadwalanController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\ValveController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Penggunaan
Route::post('/penggunaan', [PenggunaanController::class, 'store']);
Route::get('/penggunaan', [PenggunaanController::class, 'index']);

// Klasifikasi
Route::get('/klasifikasi/{nodeId}/{tahun}/{bulan}', [KlasifikasiController::class, 'hitung']);

// Penjadwalan
Route::post('/penjadwalan', [PenjadwalanController::class, 'store']);
Route::get('/penjadwalan/{nodeId}', [PenjadwalanController::class, 'index']);

// Gateway
Route::get('/gateways', [GatewayController::class, 'index']);
Route::post('/gateways', [GatewayController::class, 'store']);
Route::delete('/gateways/{id}', [GatewayController::class, 'destroy']);

// Node
Route::get('/gateways/{gatewayId}/nodes', [NodeController::class, 'index']);
Route::post('/nodes', [NodeController::class, 'store']);
Route::put('/nodes/{id}', [NodeController::class, 'update']);
Route::delete('/nodes/{id}', [NodeController::class, 'destroy']);

// Valve
Route::post('/nodes/{nodeId}/valve', [ValveController::class, 'update']);

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Profile
Route::put('/profile', [AuthController::class, 'updateProfile'])
    ->middleware('auth:sanctum');