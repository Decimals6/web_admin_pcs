@extends('layouts.admin')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Surat Jalan Pembelian</h1>
            <a href="{{ route('pembelian.delivery-note.create') }}" class="btn btn-primary">
                Buat Surat Jalan
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari No SJ / No PO / Supplier..." class="form-control">
                <button class="btn btn-secondary">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:70px;">No.</th>
                        <th>No. Surat Jalan</th>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total Barang</th>
                        <th style="width:200px;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($deliveryNotes as $dn)
                        <tr>
                            <td>
                                {{ ($deliveryNotes->currentPage() - 1) * $deliveryNotes->perPage() + $loop->iteration }}
                            </td>

                            <td>{{ $dn->no }}</td>

                            <td>{{ $dn->order?->no ?? '-' }}</td>

                            <td>{{ \Carbon\Carbon::parse($dn->tgl)->format('d-m-Y') }}</td>

                            <td>{{ $dn->order?->supplier?->nama_supplier ?? '-' }}</td>

                            <td>{{ $dn->details->sum('qty') }}</td>

                            <td class="text-nowrap"> {{-- Biar tombol nggak kepotong ke bawah kalau layar sempit --}}
                                <div class="d-flex align-items-center gap-1">

                                    {{-- Button Detail --}}
                                    <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $dn->id }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>

                                    {{-- Link Edit --}}
                                    <a href="{{ route('pembelian.delivery-note.edit', $dn->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    {{-- Form Hapus --}}
                                    <form action="{{ route('pembelian.delivery-note.destroy', $dn->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus surat jalan ini?')" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                Belum ada surat jalan pembelian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- INFO + PAGINATION --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $deliveryNotes->firstItem() ?? 0 }}
                –
                {{ $deliveryNotes->lastItem() ?? 0 }}
                dari {{ $deliveryNotes->total() }} data
            </div>

            <div>
                {{ $deliveryNotes->links('pagination::bootstrap-5') }}
            </div>
        </div>

    </div>
@endsection
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Surat Jalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                let id = this.dataset.id;

                fetch('/dnpo/' + id)
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('detailContent').innerHTML = data;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    });
            });
        });
    });
</script>