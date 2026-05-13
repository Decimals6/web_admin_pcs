<?php

namespace App\Http\Controllers;

use App\Models\PremiUser;
use App\Models\User;
use Illuminate\Http\Request;

class PremiUserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $premis = PremiUser::with('user')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                });
            })->paginate(10);

        return view('absensi.premi-karyawan.index', compact('premis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nominal' => 'required|numeric',
        ]);

        PremiUser::create($data);
        return redirect()->route('absensi.premi-karyawan.index')->with('success', 'Premi user ditambahkan.');
    }

    public function update(Request $request, PremiUser $premiUser)
    {
        $premiUser->update($request->all());
        return redirect()->route('absensi.premi-karyawan.index')->with('success', 'Premi updated.');
    }

    public function destroy(PremiUser $premiUser)
    {
        $premiUser->delete();
        return redirect()->route('absensi.premi-karyawan.index');
    }

    

    public function create()
    {
        // Hanya ambil user yang BELUM ada di tabel premi_users
        $users = User::whereDoesntHave('premiUser')->get();
        return view('absensi.premi-karyawan.create', compact('users'));
    }
}