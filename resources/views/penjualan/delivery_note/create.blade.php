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
                        style="background-color: #e9ecef;" readonly>
                </div>

                <div class="col-md-3">
                    <label>Tanggal</label>
                    <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label>Sales Order</label>
                    <select name="order_id" id="orderSelect" class="form-control">
                        <option value="">-- Pilih Sales Order --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">
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
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4>Detail Barang</h4>
                <button type="button" class="btn btn-sm btn-success" id="btnTambahBaris" onclick="addAvailableRow()">+
                    Tambah Barang</button>
            </div>

            <table class="table" id="detailTable">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Keterangan</th>
                        <th style="width: 50px;">Aksi</th>
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
        let currentOrderDetails = []; // Menyimpan data detail order yang sedang aktif

        function addRow(detail = null) {
            if (!detail) return;

            const tbody = document.querySelector('#detailTable tbody');
            let row = document.createElement('tr');

            // Mencari nama barang, antisipasi jika struktur object-nya bertingkat
            let namaBarang = detail.barang ? detail.barang.nama_barang : (detail.nama || 'Tanpa Nama');

            row.innerHTML = `
                    <td>
                        <input type="hidden" class="order-detail-id-input" name="details[${rowIndex}][order_detail_id]" value="${detail.id}">
                        <input type="text" class="form-control" value="${namaBarang}" readonly>
                    </td>
                    <td>
                        <input type="number" 
                            name="details[${rowIndex}][qty]" 
                            class="form-control qty-input" 
                            min="1"
                            max="${detail.sisa}"
                            value="${detail.sisa}">
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

        // Perbaikan 2: Fungsi untuk menambah kembali item dari SO yang belum ada di tabel
        function addAvailableRow() {
            if (currentOrderDetails.length === 0) {
                alert('Silahkan pilih Sales Order terlebih dahulu.');
                return;
            }

            // Ambil semua order_detail_id yang saat ini sudah ada di tabel
            const existingIds = Array.from(document.querySelectorAll('.order-detail-id-input'))
                .map(input => parseInt(input.value));

            // Cari item dari currentOrderDetails yang BELUM ada di tabel dan sisanya > 0
            const itemToRestore = currentOrderDetails.find(detail => {
                let sisa = detail.qty - (detail.qty_sent || 0);
                return sisa > 0 && !existingIds.includes(detail.id);
            });

            if (itemToRestore) {
                let sisa = itemToRestore.qty - (itemToRestore.qty_sent || 0);
                itemToRestore.sisa = sisa;
                addRow(itemToRestore);
            } else {
                alert('Semua barang dari Sales Order ini sudah dimasukkan ke dalam tabel.');
            }
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
                currentOrderDetails = []; // Reset penampung data

                if (!orderId) return;

                fetch(`/order/${orderId}/details`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.length === 0) {
                            console.log("Order detail kosong");
                            return;
                        }

                        // Simpan data ke variable global agar bisa diakses tombol "+"
                        currentOrderDetails = data;

                        data.forEach(detail => {
                            let sisa = detail.qty - (detail.qty_sent || 0);
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