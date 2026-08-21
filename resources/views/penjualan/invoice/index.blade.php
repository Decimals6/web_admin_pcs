@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between">
                <h4 class="mb-2">Daftar Invoice Penjualan</h4>
                <a href="{{ route('penjualan.invoice.create') }}" class="btn btn-primary mb-2">
                    + Buat Invoice Keluar
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- ================= FILTER ================= --}}
            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label>Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        <option value="">-- Semua Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->nama_customer }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('penjualan.invoice.index') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
            {{-- ================= END FILTER ================= --}}

            <table class="table table-bordered table-striped">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>No.</th>
                        <th>Tanggal Faktur</th>
                        <th>No. Faktur</th>
                        <th>No. PO</th>
                        <th>Customer</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $index => $invoice)
                        <tr>
                            <td>{{ $invoices->firstItem() + $index }}</td>
                            <td>{{ $invoice->tgl->format('d-m-Y') }}</td>
                            <td>{{ $invoice->no }}</td>
                            <td>{{ $invoice->no_so ?? '-' }}</td>
                            <td>{{ $invoice->customer->nama_customer ?? '-' }}</td>
                            <td>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-detail" data-id="{{ $invoice->id }}">
                                    Detail
                                </button>

                                {{-- Tombol Edit Penjualan Invoice --}}
                                <a href="{{ route('penjualan.invoice.edit', $invoice->id) }}" class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                {{-- Tombol Hapus Invoice --}}
                                <button type="button" class="btn btn-danger btn-sm btn-delete-invoice"
                                    data-id="{{ $invoice->id }}" data-no="{{ $invoice->no }}">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- ================= INFO + PAGINATION ================= --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan
                    {{ $invoices->firstItem() ?? 0 }}
                    –
                    {{ $invoices->lastItem() ?? 0 }}
                    dari
                    {{ $invoices->total() }} data
                </div>

                <div>
                    {{ $invoices->links() }}
                </div>
            </div>

        </div>
    </div>
@endsection

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-body-detail">
                Loading...
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Invoice -->
<div class="modal fade" id="deleteInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Peringatan Hapus Invoice
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="formDeleteInvoice" method="POST" action="">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin menghapus invoice <strong id="delete_invoice_no"></strong>?
                    </p>

                    <!-- Loading state -->
                    <div id="loading_relations" class="text-center py-3 text-muted">
                        <i class="fas fa-spinner fa-spin"></i> Memeriksa data terkait...
                    </div>

                    <!-- Area Data Terkait -->
                    <div id="relations_container" style="display: none;">
                        <div class="alert alert-warning mb-2 py-2">
                            <small class="d-block font-weight-bold">
                                <i class="fas fa-info-circle"></i> Perhatian: Menghapus invoice ini akan menghapus semua
                                data berikut secara permanen:
                            </small>
                        </div>
                        <ul class="list-group list-group-flush border rounded mb-2" id="relations_list">
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

                fetch('/invSales/' + id + '/detail')
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('modal-body-detail').innerHTML = data;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    })
                    .catch(err => console.error(err));
            });
        });

        const deleteModal = $('#deleteInvoiceModal');
        const deleteForm = document.getElementById('formDeleteInvoice');
        const invoiceNoSpan = document.getElementById('delete_invoice_no');
        const loadingBox = document.getElementById('loading_relations');
        const relationsContainer = document.getElementById('relations_container');
        const relationsList = document.getElementById('relations_list');

        document.querySelectorAll('.btn-delete-invoice').forEach(button => {
            button.addEventListener('click', function () {
                const invoiceId = this.getAttribute('data-id');
                const invoiceNo = this.getAttribute('data-no');

                invoiceNoSpan.innerText = invoiceNo;
                deleteForm.action = `/penjualan/invoice/${invoiceId}`;

                // Reset state tampilan modal
                loadingBox.style.display = 'block';
                relationsContainer.style.display = 'none';
                relationsList.innerHTML = '';

                deleteModal.modal('show');

                // Cek data terkait ke API
                fetch(`/penjualan/invoice/${invoiceId}/check-relations`)
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
                            Riwayat Pembayaran (Payment) 
                            <span class="badge ${data.payments_count > 0 ? 'badge-danger' : 'badge-success'} badge-pill">
                                ${data.payments_count} transaksi (${data.paid_formatted})
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            Relasi Delivery Note 
                            <span class="badge badge-info badge-pill">${data.dn_count} Surat Jalan</span>
                        </li>
                    `;
                        relationsList.innerHTML = content;
                    })
                    .catch(err => {
                        loadingBox.style.display = 'none';
                        relationsContainer.style.display = 'block';
                        relationsList.innerHTML = `<li class="list-group-item text-danger py-2">Gagal memuat rincian relasi.</li>`;
                    });
            });
        });
    });


</script>