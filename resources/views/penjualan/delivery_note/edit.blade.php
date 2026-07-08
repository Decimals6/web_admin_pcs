@extends('layouts.admin')

@section('content')
    <h1>Edit Surat Jalan Penjualan</h1>

    <!-- Form dialihkan ke route update dengan ID dan tambahkan @method('PUT') -->
    <form action="{{ route('penjualan.delivery-note.update', $deliveryNote->id) }}" method="POST">
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
                    <input type="date" name="tgl" class="form-control"
                        value="{{ date('Y-m-d', strtotime($deliveryNote->tgl)) }}" required>
                </div>

                <div class="col-md-3">
                    <label>Sales Order</label>
                    <select name="order_id" class="form-control">
                        <option value="">-- Pilih Order --</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" {{ $deliveryNote->order_id == $order->id ? 'selected' : '' }}>
                                {{ $order->no }} - {{ $order->customer->nama_customer ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Alamat Customer</label>
                    <input type="text" name="alamat_kirim" class="form-control" value="{{ $deliveryNote->alamat_kirim }}"
                        required>
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
                    <!-- Baris data lama akan otomatis digenerate oleh JavaScript di bawah -->
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('penjualan.delivery-note.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>

    <script>
        let rowIndex = 0;
        let currentOrderDetails = []; // Menyimpan data detail order yang sedang aktif (mirip create.blade)

        function addRow(detail = null) {
            if (!detail) return;

            const tbody = document.querySelector('#detailTable tbody');
            let row = document.createElement('tr');

            row.innerHTML = `
                    <td>
                        <input type="hidden" class="order-detail-id-input" name="details[${rowIndex}][order_detail_id]" value="${detail.order_detail_id}">
                        <input type="text" class="form-control" value="${detail.nama_barang}" readonly>
                    </td>
                    <td>
                        <input type="number" 
                            name="details[${rowIndex}][qty]" 
                            class="form-control qty-input" 
                            min="1"
                            ${detail.sisa !== undefined ? `max="${detail.sisa}"` : ''}
                            value="${detail.qty}">
                    </td>
                    <td>
                        <input type="text" name="details[${rowIndex}][keterangan]" class="form-control" value="${detail.keterangan || ''}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button>
                    </td>
                `;

            tbody.appendChild(row);
            rowIndex++;
        }

        // Perbaikan: fungsi untuk menambah kembali item dari SO yang sudah dihapus dari tabel
        // (logic sama seperti create.blade)
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
                addRow({
                    order_detail_id: itemToRestore.id,
                    nama_barang: itemToRestore.barang.nama_barang,
                    qty: sisa,
                    sisa: sisa,
                    keterangan: ''
                });
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

        // Ambil detail order dari server. autoRenderAll = true dipakai saat user GANTI Sales Order
        // (langsung render semua barang sisa), autoRenderAll = false dipakai saat load awal halaman
        // (cuma isi currentOrderDetails, karena baris tabel sudah ada dari existingDetails).
        function loadOrderDetails(orderId, autoRenderAll) {
            return fetch(`/order/${orderId}/details`)
                .then(res => res.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        currentOrderDetails = [];
                        return;
                    }

                    currentOrderDetails = data;

                    if (autoRenderAll) {
                        data.forEach(detail => {
                            let sisa = detail.qty - (detail.qty_sent || 0);
                            if (sisa > 0) {
                                addRow({
                                    order_detail_id: detail.id,
                                    nama_barang: detail.barang.nama_barang,
                                    qty: sisa,
                                    sisa: sisa,
                                    keterangan: ''
                                });
                            }
                        });
                    }
                })
                .catch(err => console.log(err));
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Ambil data detail bawaan dari server via Blade (ditampung ke array JS)
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

            const orderSelect = document.querySelector('select[name="order_id"]');

            // Perbaikan: begitu halaman edit dibuka dan sudah ada Sales Order terpilih,
            // langsung ambil daftar detail SO-nya supaya tombol "+ Tambah Barang" bisa
            // langsung dipakai tanpa harus ganti-ganti Sales Order dulu.
            if (orderSelect.value) {
                loadOrderDetails(orderSelect.value, false);
            }

            // Logika ganti Sales Order (tetap dipertahankan jika user ingin mengganti SO di tengah jalan)
            orderSelect.addEventListener('change', function () {
                const orderId = this.value;
                const tbody = document.querySelector('#detailTable tbody');

                tbody.innerHTML = '';
                rowIndex = 0;
                currentOrderDetails = [];

                if (!orderId) return;

                loadOrderDetails(orderId, true);
            });
        });
    </script>
@endsection