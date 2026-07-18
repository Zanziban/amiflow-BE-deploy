<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        // cek user ada & password cocok
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        // buat token (Sanctum)
        $token = $user->createToken('amiflow-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        // hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);
        $user = $request->user();
        $user->name = $data['name'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return response()->json([
            'message' => 'Profile berhasil diperbarui',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

    }
}