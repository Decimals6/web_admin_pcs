@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between">
                <h4 class="mb-2">Daftar Invoice Pembelian</h4>
                <a href="{{ route('pembelian.invoice.create') }}" class="btn btn-primary mb-2">
                    + Buat Invoice Masuk
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
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('pembelian.invoice.index') }}" class="btn btn-secondary">
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
                        <th>Supplier</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $index => $invoice)
                        <tr>
                            {{-- nomor urut lanjut --}}
                            <td>{{ $invoices->firstItem() + $index }}</td>
                            <td>{{ $invoice->tgl->format('d-m-Y') }}</td>
                            <td>{{ $invoice->no }}</td>
                            <td>{{ $invoice->no_so ?? '-' }}</td>
                            <td>{{ $invoice->supplier->nama_supplier ?? '-' }}</td>
                            <td>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                            <td>
                                <button class="btn btn-info btn-sm btn-detail" data-id="{{ $invoice->id }}">
                                    Detail
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-detail').forEach(btn => {
            btn.addEventListener('click', function () {
                let id = this.dataset.id;

                fetch('/invPurchase/' + id + '/detail')
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('modal-body-detail').innerHTML = data;
                        new bootstrap.Modal(document.getElementById('detailModal')).show();
                    })
                    .catch(err => console.error(err));
            });
        });
    });
</script>