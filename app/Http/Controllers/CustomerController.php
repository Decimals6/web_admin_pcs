<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
{
    $query = \App\Models\Customer::query();

    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('kode_customer', 'like', '%' . $request->search . '%')
              ->orWhere('nama_customer', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%')
              ->orWhere('telepon', 'like', '%' . $request->search . '%');
        });
    }

    $customers = $query->paginate(10)->withQueryString();

    return view('customers.index', compact('customers'));
}


    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_customer' => 'required|unique:customers',
            'nama_customer' => 'required',
        ]);

        Customer::create($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil ditambahkan');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'kode_customer' => 'required|unique:customers,kode_customer,' . $customer->id,
            'nama_customer' => 'required',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil diupdate');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer berhasil dihapus');
    }
}
