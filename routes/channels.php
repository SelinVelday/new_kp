<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan semua saluran penyiaran acara yang didukung
| oleh aplikasi Anda. Callback otorisasi saluran akan dipanggil.
|
*/

// Izin untuk channel Project (agar board bisa digeser real-time)
Broadcast::channel('project.{id}', function ($user, $id) {
    // Kembalikan true jika user adalah anggota project tersebut
    return $user->projects->contains($id);
});

// --- TAMBAHKAN INI (PENTING UNTUK NOTIFIKASI) ---
// Izin untuk channel User (agar notifikasi lonceng muncul)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // User hanya boleh mendengar channel miliknya sendiri
    return (int) $user->id === (int) $id;
});