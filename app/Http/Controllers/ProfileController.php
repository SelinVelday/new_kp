<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * 1. Menampilkan Halaman Edit Profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * 2. Proses Update Profil (Nama, Email, Avatar)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // A. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        // B. Update Data Teks
        $user->name = $request->name;
        $user->email = $request->email;

        // C. Logic Upload Avatar
        if ($request->hasFile('avatar')) {
            // 1. Hapus foto lama jika ada (agar storage tidak penuh)
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 2. Simpan foto baru ke folder 'avatars' (public disk)
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // 3. Update path di database
            $user->avatar = $path;
        }

        // D. Simpan Perubahan ke Database
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * 3. Proses Ganti Tema (Dark/Light Mode) - via AJAX
     * Dipanggil oleh Javascript saat tombol matahari/bulan diklik.
     */
    public function updateTheme(Request $request)
    {
        // Validasi: Hanya menerima 'light' atau 'dark'
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $user = Auth::user();
        $user->theme = $request->theme;
        $user->save(); // Simpan preferensi tema ke database

        return response()->json([
            'success' => true,
            'message' => 'Tema berhasil disimpan.'
        ]);
    }
}