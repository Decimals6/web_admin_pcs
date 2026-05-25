@extends('layouts.admin')

@section('content')
    <h1>Edit Surat Jalan Pembelian</h1>

    <!-- 1. Form dialihkan ke route update dengan ID dan tambahkan @method('PUT') -->
    <form action="{{ route('pembelian.delivery-note.update', $deliveryNote->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>No Surat Jalan</label>
                    <input type="text" name="no" class="form-control" value="{{ $deliveryNote->no }}" required>
                </div>

                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d', strtotime($deliveryNote->tgl)) }}" required>
                </div>

                <div class="col-md-3">
                    <label>Purchase Order</label>
                    <select name="order_id" class="form-control">
                        <option value="">-- Pilih Order --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ $deliveryNote->order_id == $order->id ? 'selected' : '' }}>
                                {{ $order->no }} - {{ $order->supplier->nama_supplier ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Alamat Supplier</label>
                    <input type="text" name="alamat_kirim" class="form-control" value="{{ $deliveryNote->alamat_kirim }}"
                        required>
                </div>
            </div>

            <hr>
            <h4>Detail Barang</h4>
            <table class="table" id="detailTable">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Keterangan</th>
                        <th><button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris data lama akan otomatis digenerate oleh JavaScript di bawah -->
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('pembelian.delivery-note.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>

    <script>
        let rowIndex = 0;

        function addRow(detail = null) {
            const tbody = document.querySelector('#detailTable tbody');
            let row = document.createElement('tr');

            row.innerHTML = `
                    <td>
                        <input type="hidden" name="details[${rowIndex}][order_detail_id]" value="${detail ? detail.order_detail_id : ''}">
                        <input type="text" class="form-control" value="${detail ? detail.nama_barang : ''}" readonly>
                    </td>
                    <td>
                        <input type="number" 
                            name="details[${rowIndex}][qty]" 
                            class="form-control qty-input" 
                            min="1"
                            value="${detail ? detail.qty : 1}">
                    </td>
                    <td>
                        <input type="text" name="details[${rowIndex}][keterangan]" class="form-control" value="${detail ? (detail.keterangan || '') : ''}">
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
            // 🔥 Ambil data detail bawaan dari server via Blade (ditampung ke array JS)
            const existingDetails = [
                @foreach($deliveryNote->details as $d)
                        {
                        order_detail_id: "{{ $d->order_detail_id }}",
                        nama_barang: "{{ $d->orderDetail->barang->nama_barang }}",
                        qty: "{{ $d->qty }}",
                        keterangan: "{{ $d->keterangan }}"
                    },
                @endforeach
                ];

            // Render detail yang sudah ada saat halaman pertama kali dibuka
            existingDetails.forEach(detail => addRow(detail));

            // Logika ganti PO (tetap dipertahankan jika user ingin mengganti PO di tengah jalan)
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
                        if (!data || data.length === 0) return;

                        data.forEach(detail => {
                            let sisa = detail.qty - detail.qty_sent;
                            if (sisa > 0) {
                                addRow({
                                    order_detail_id: detail.id,
                                    nama_barang: detail.barang.nama_barang,
                                    qty: sisa,
                                    keterangan: ''
                                });
                            }
                        });
                    })
                    .catch(err => console.log(err));
            });
        });
    </script>
@endsection