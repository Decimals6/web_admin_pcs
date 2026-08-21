@extends('layouts.admin')

@section('content')
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Delivery Note Penjualan</h1>
            <a href="{{ route('penjualan.delivery-note.create') }}" class="btn btn-primary">
                Buat Delivery Note
            </a>
        </div>

        {{-- SEARCH --}}
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari No DN / No SO / Customer..." class="form-control">
                <button class="btn btn-secondary">Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:70px;">No.</th>
                        <th>No. Delivery Note</th>
                        <th>No. SO</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
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

                            <td>{{ $dn->order?->customer?->nama_customer ?? '-' }}</td>

                            <td>{{ $dn->details->sum('qty') }}</td>

                            <td class="text-nowrap">
                                <div class="d-flex align-items-center gap-1">

                                    {{-- Button Detail --}}
                                    <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $dn->id }}">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>

                                    {{-- Link Edit --}}
                                    <a href="{{ route('penjualan.delivery-note.edit', $dn->id) }}"
                                        class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>

                                    {{-- Button Hapus Modal --}}
                                    <button type="button" class="btn btn-sm btn-danger btn-delete-dn" data-id="{{ $dn->id }}"
                                        data-no="{{ $dn->no }}">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                Belum ada delivery note
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

<!-- Modal Konfirmasi Hapus Delivery Note -->
<div class="modal fade" id="deleteDnModal" tabindex="-1" role="dialog" aria-labelledby="deleteDnModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteDnModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Peringatan Hapus Surat Jalan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formDeleteDn" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus Surat Jalan <strong id="delete_dn_no"></strong>?
                    </p>

                    <!-- Loading state -->
                    <div id="loading_dn_relations" class="text-center py-3 text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Memeriksa data terkait...
                    </div>

                    <!-- Area Data Terkait -->
                    <div id="dn_relations_container" style="display: none;">
                        <div class="alert alert-warning mb-2 py-2">
                            <small class="d-block font-weight-bold">
                                <i class="fas fa-info-circle"></i> Perhatian: Penghapusan ini akan berdampak pada data
                                berikut:
                            </small>
                        </div>
                        <ul class="list-group list-group-flush border rounded mb-2" id="dn_relations_list">
                            <!-- Injected by JavaScript -->
                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Ya, Hapus Semua</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                let id = this.dataset.id;

                fetch('/dnso/' + id)
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('detailContent').innerHTML = data;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    });
            });
        });

        const deleteModal = $('#deleteDnModal');
        const deleteForm = document.getElementById('formDeleteDn');
        const dnNoSpan = document.getElementById('delete_dn_no');
        const loadingBox = document.getElementById('loading_dn_relations');
        const relationsContainer = document.getElementById('dn_relations_container');
        const relationsList = document.getElementById('dn_relations_list');

        document.querySelectorAll('.btn-delete-dn').forEach(button => {
            button.addEventListener('click', function () {
                const dnId = this.getAttribute('data-id');
                const dnNo = this.getAttribute('data-no');

                dnNoSpan.innerText = dnNo;
                deleteForm.action = `/penjualan/delivery-note/${dnId}`;

                // Reset state tampilan modal
                loadingBox.style.display = 'block';
                relationsContainer.style.display = 'none';
                relationsList.innerHTML = '';

                deleteModal.modal('show');

                // Request cek relasi via API
                fetch(`/penjualan/delivery-note/${dnId}/check-relations`)
                    .then(res => res.json())
                    .then(data => {
                        loadingBox.style.display = 'none';
                        relationsContainer.style.display = 'block';

                        let content = `
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            Item Barang Detail 
                            <span class="badge badge-secondary badge-pill">${data.details_count} item</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            Penyesuaian Stok (Rollback)
                            <span class="badge badge-info badge-pill">${data.stock_impact}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            Terkait Tagihan Invoice 
                            <span class="badge ${data.invoices_count > 0 ? 'badge-danger' : 'badge-success'} badge-pill">
                                ${data.invoices_count} Invoice
                            </span>
                        </li>
                    `;
                        relationsList.innerHTML = content;
                    })
                    .catch(err => {
                        loadingBox.style.display = 'none';
                        relationsContainer.style.display = 'block';
                        relationsList.innerHTML = `<li class="list-group-item text-danger py-2">Gagal memeriksa dependensi surat jalan.</li>`;
                    });
            });
        });
    });
</script>