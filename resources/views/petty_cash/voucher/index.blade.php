@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between">
            <h4>Voucher Reimburse Petty Cash</h4>
            <a href="{{ route('petty_cash.voucher.create') }}" class="btn btn-primary mb-3">
                + Buat Voucher
            </a>
        </div>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>No Voucher</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Akhir</th>
                    <th class="text-end">Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td>{{ $row->no }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tgl_mulai)->format('d-m-Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->tgl_akhir)->format('d-m-Y') }}</td>

                        <td class="text-end fw-bold">
                            {{ number_format($row->total) }}
                        </td>
                        <td>
                            <div class="d-flex">
                                <button class="btn btn-sm btn-info btn-detail mr-2" data-id="{{ $row->id }}">
                                    Detail
                                </button>

                                <form action="{{ route('petty_cash.voucher.destroy', $row->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Transaksi kas di dalamnya akan dilepas kembali.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada voucher</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', function () {

                    let id = this.dataset.id;

                    fetch(`/petty_cash/voucher/${id}/detail`)
                        .then(res => res.text())
                        .then(data => {

                            document.getElementById('detailContent').innerHTML = data;

                            new bootstrap.Modal(document.getElementById('detailModal')).show();
                        })
                        .catch(err => console.log(err));
                });
            });

        });
    </script>
@endsection