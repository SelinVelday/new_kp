<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\User;
use App\Models\Role; // Pastikan Model Role diimport
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        // Ambil team dimana user ini menjadi anggotanya
        $teams = Auth::user()->teams; 
        
        // Ambil user lain untuk diundang (kecuali diri sendiri)
        $users = User::where('id', '!=', Auth::id())->get();

        return view('teams.index', compact('teams', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // --- PERBAIKAN UTAMA: OTOMATIS BUAT ROLE JIKA KOSONG ---
        // Mencari role 'owner', jika tidak ada maka buat baru
        $ownerRole = Role::firstOrCreate(
            ['slug' => 'owner'], // Cari berdasarkan slug
            ['name' => 'Owner']  // Jika tidak ada, buat dengan nama ini
        );

        // 1. Buat Tim
        $team = Team::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . uniqid()), // Generate Slug Unik
            'owner_id' => Auth::id(),
            // 'description' => $request->description // HAPUS baris ini jika kolom description tidak ada di database
        ]);

        // 2. Masukkan User pembuat sebagai Member dengan Role Owner
        $team->members()->attach(Auth::id(), ['role_id' => $ownerRole->id]);

        return redirect()->back()->with('success', 'Tim berhasil dibuat!');
    }
}