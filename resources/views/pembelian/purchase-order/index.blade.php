@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white align-items-center">
            <div class="d-flex justify-content-between">
                <h4>Purchase Orders</h4>
                <a href="{{ route('pembelian.purchase-order.create') }}" class="btn btn-success btn-sm">
                    + Buat PO
                </a>
            </div>
        </div>

        <div class="card-body">
            {{-- SEARCH --}}
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari No PO / Supplier..."
                        value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="po-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Tanggal PO</th>
                            <th>No. PO</th>
                            <th>Supplier</th>
                            <th class="text-end">Nominal</th>
                            <th>Keterangan</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $po)
                            <tr>
                                <td>
                                    {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ $po->tgl->format('d-m-Y') }}</td>
                                <td>{{ $po->no }}</td>
                                <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                                <td class="text-end">{{ number_format($po->dpp, 0, ',', '.') }}</td>
                                <td>{{ $po->keterangan ?? '-' }}</td>
                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-1">
                                        {{-- Tombol Detail - Menggunakan data-id untuk di-grab oleh JavaScript/AJAX Modal --}}
                                        <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $po->id }}">
                                            Detail
                                        </button>

                                        {{-- Tombol Edit - Langsung lempar ke halaman edit membawa ID PO nya --}}
                                        <a href="{{ route('pembelian.purchase-order.edit', $po->id) }}"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <button type="button" class="btn btn-sm btn-danger btn-delete-so"
                                            data-id="{{ $po->id }}" data-no="{{ $po->no }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Data PO belum tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- INFO + PAGINATION --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $orders->firstItem() ?? 0 }} – {{ $orders->lastItem() ?? 0 }} dari {{ $orders->total() }}
                    data
                </div>
                <div>
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Mengambil data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<div class="modal fade" id="deleteSoModal" tabindex="-1" role="dialog" aria-labelledby="deleteSoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteSoModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Peringatan Hapus Sales Order
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formDeleteSo" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus Sales Order <strong id="delete_so_no"></strong>?
                    </p>

                    <!-- Loading state -->
                    <div id="loading_so_relations" class="text-center py-3 text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Memeriksa data terkait...
                    </div>

                    <!-- Area Data Terkait -->
                    <div id="so_relations_container" style="display: none;">
                        <div class="alert alert-warning mb-2 py-2">
                            <small class="d-block font-weight-bold">
                                <i class="fas fa-info-circle"></i> Perhatian: Menghapus Sales Order ini akan menghapus
                                semua cascading data berikut secara permanen:
                            </small>
                        </div>
                        <ul class="list-group list-group-flush border rounded mb-2" id="so_relations_list">
                            <!-- Diinjeksi oleh JavaScript -->
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

@section('scripts')
    <script>
        $(document).ready(function () {
            // Gunakan delegasi event agar tombol tetap jalan meski ada pagination/search
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));

                // Tampilkan loading saat proses fetch
                $('#detailContent').html(`
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Loading...</p>
                            </div>
                        `);
                modal.show();

                // AJAX Request (Pastikan route ini sudah ada di web.php)
                // Saya sesuaikan dengan route fetch di script lama kamu: /po/{id}
                $.get('/po/' + id, function (data) {
                    $('#detailContent').html(data);
                }).fail(function () {
                    $('#detailContent').html('<div class="alert alert-danger">Gagal mengambil data. Pastikan Route sudah benar.</div>');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = $('#deleteSoModal');
            const deleteForm = document.getElementById('formDeleteSo');
            const soNoSpan = document.getElementById('delete_so_no');
            const loadingBox = document.getElementById('loading_so_relations');
            const relationsContainer = document.getElementById('so_relations_container');
            const relationsList = document.getElementById('so_relations_list');

            document.querySelectorAll('.btn-delete-so').forEach(button => {
                button.addEventListener('click', function () {
                    const soId = this.getAttribute('data-id');
                    const soNo = this.getAttribute('data-no');

                    soNoSpan.innerText = soNo;
                    deleteForm.action = `/penjualan/sales-order/${soId}`;

                    loadingBox.style.display = 'block';
                    relationsContainer.style.display = 'none';
                    relationsList.innerHTML = '';

                    deleteModal.modal('show');

                    fetch(`/penjualan/sales-order/${soId}/check-relations`)
                        .then(res => res.json())
                        .then(data => {
                            loadingBox.style.display = 'none';
                            relationsContainer.style.display = 'block';

                            let content = `
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    Item Barang Detail (Order Details)
                                    <span class="badge badge-secondary badge-pill">${data.details_count} item</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    Surat Jalan (Delivery Notes)
                                    <span class="badge ${data.dn_count > 0 ? 'badge-danger' : 'badge-success'} badge-pill">
                                        ${data.dn_count} Dokumen
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    Invoice Terkait
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
                            relationsList.innerHTML = `<li class="list-group-item text-danger py-2">Gagal memeriksa dependensi Sales Order.</li>`;
                        });
                });
            });
        });
    </script>
@endsection