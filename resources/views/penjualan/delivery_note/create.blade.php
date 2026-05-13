@extends('layouts.admin')

@section('content')
    <h1>Surat Jalan Penjualan</h1>

    <form action="{{ route('penjualan.delivery-note.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>No. Invoice</label>
                    <input type="text" name="no" class="form-control"
                        value="{{ generateDocumentNumber('delivery_notes', 'PCS-SJ', 'keluar') }}" 
                        style="background-color: #e9ecef;">
                </div>

                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                {{-- <div class="col-md-3">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="masuk">Masuk</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </div> --}}
                <div class="col-md-3">
                    <label>Sales Order</label>
                    <select name="order_id" id="orderSelect" class="form-control">
                        <option value="">-- Pilih Sales Order --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" data-details='@json($order->details->map(function ($d) {
                                return [
                                    "id" => $d->id,
                                    "nama" => $d->barang->nama_barang,
                                    "qty" => $d->qty
                                ];
                            }))'>
                                {{ $order->no }} - {{ $order->customer->nama_customer ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Alamat Kirim</label>
                    <input type="text" name="alamat_kirim" class="form-control" required>
                </div>
            </div>

            <hr>
            <h4>Detail Barang</h4>
            <table class="table" id="detailTable">
                <thead>
                    <tr>
                        <th>Barang</th>

                        <th>Keterangan</th>
                        <th><button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('penjualan.delivery-note.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>

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

                    <td>
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

        document.addEventListener('DOMContentLoaded', function () {

            const orderSelect = document.querySelector('select[name="order_id"]');

            orderSelect.addEventListener('change', function () {

                const orderId = this.value;
                const tbody = document.querySelector('#detailTable tbody');

                tbody.innerHTML = '';
                rowIndex = 0;

                if (!orderId) return;

                fetch(`/order/${orderId}/details`)
                    .then(res => res.json())
                    .then(data => {

                        if (!data || data.length === 0) {
                            console.log("Order detail kosong");
                            return;
                        }

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