@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Surat Jalan Pembelian</h4>
        </div>

        <form action="{{ route('pembelian.delivery-note.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>No Surat Jalan</label>
                        <input type="text" name="no" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Purchase Order</label>
                        <select name="order_id" id="orderSelect" class="form-control select2-search" data-placeholder="-- Cari No. PO / Supplier --">
                            <option value="" data-alamat="">-- Cari No. PO / Supplier --</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" data-alamat="{{ $order->supplier->alamat ?? '' }}">
                                    {{ $order->no }} | {{ \Carbon\Carbon::parse($order->tgl)->format('d-m-Y') }} | {{ $order->supplier->nama_supplier ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Alamat Supplier</label>
                        <input type="text" name="alamat_kirim" id="alamatKirim" class="form-control" required>
                    </div>
                </div>

                <hr>
                <h4>Detail Barang</h4>
                <table class="table table-bordered" id="detailTable">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th style="width:15%">Qty</th>
                            <th>Keterangan</th>
                            <th style="width:60px" class="text-center"><button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('pembelian.delivery-note.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ced4da !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding-left: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }
</style>

@section('scripts')
    <script>
        let rowIndex = 0;

        function addRow(detail = null) {
            const tbody = document.querySelector('#detailTable tbody');
            let row = document.createElement('tr');

            row.innerHTML = `
                <td>
                    <input type="hidden" name="details[${rowIndex}][order_detail_id]" value="${detail ? detail.id : ''}">
                    <input type="text" class="form-control" value="${detail ? detail.barang.nama_barang : ''}" readonly>
                </td>
                <td>
                    <input type="number"
                        name="details[${rowIndex}][qty]"
                        class="form-control qty-input"
                        min="1"
                        value="${detail ? detail.sisa : 1}">
                </td>
                <td>
                    <input type="text" name="details[${rowIndex}][keterangan]" class="form-control">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button>
                </td>
            `;

            tbody.appendChild(row);
            rowIndex++;
        }

        function removeRow(btn) {
            const tbody = document.querySelector('#detailTable tbody');
            btn.closest('tr').remove();

            if (tbody.rows.length === 0) {
                rowIndex = 0;
            }
        }

        $(document).ready(function () {
            $('#orderSelect').select2({
                placeholder: "-- Cari No. PO / Supplier --",
                allowClear: true,
                width: '100%'
            });

            $('#orderSelect').on('change', function () {
                const orderId = $(this).val();
                const alamat = $(this).find('option:selected').data('alamat') || '';
                $('#alamatKirim').val(alamat);

                const tbody = document.querySelector('#detailTable tbody');

                tbody.innerHTML = '';
                rowIndex = 0;

                if (!orderId) return;

                fetch(`/order/${orderId}/details`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.length === 0) return;

                        data.forEach(detail => {
                            let sisa = detail.qty - detail.qty_sent;
                            if (sisa > 0) {
                                detail.sisa = sisa;
                                addRow(detail);
                            }
                        });
                    })
                    .catch(err => console.log(err));
            });
        });
    </script>
@endsection
