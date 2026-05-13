@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between">
        <h4>Petty Cash & Pengeluaran Kantor</h4>
        <a href="{{ route('petty_cash.kas.create') }}" class="btn btn-primary mb-3">
            + Tambah Transaksi
        </a>
    </div>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Tanggal</th>
                <th>No Transaksi</th>
                <th>Keterangan</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Kredit</th>
                <th class="text-end">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->no_transaksi }}</td>
                <td>{{ $row->keterangan }}</td>
                <td class="text-end">
                    {{ $row->debit ? number_format($row->debit) : '-' }}
                </td>
                <td class="text-end">
                    {{ $row->kredit ? number_format($row->kredit) : '-' }}
                </td>
                <td class="text-end fw-bold">
                    {{ number_format($row->saldo) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
