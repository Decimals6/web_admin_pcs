<?php
namespace App\Http\Controllers;

use App\Models\SewaKendaraan;
use App\Models\User;
use Illuminate\Http\Request;

class SewaKendaraanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $sewas = SewaKendaraan::with('user')
            ->when($search, function($query) use ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                })->orWhere('nopol', 'like', "%$search%");
            })->paginate(10);

        return view('absensi.sewa-kendaraan.index', compact('sewas'));
    }

    public function create()
    {
        // Kuncinya di sini, Do. Hanya ambil user yang belum masuk ke master sewa
        $users = User::whereDoesntHave('sewaKendaraan')->get();
        return view('absensi.sewa-kendaraan.create', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|unique:sewa_kendaraan,user_id',
            'nopol'   => 'required|string',
            'nominal' => 'required|numeric',
        ]);

        SewaKendaraan::create($data);
        return redirect()->route('absensi.sewa-kendaraan.index')->with('success', 'Master sewa kendaraan berhasil disimpan!');
    }
}