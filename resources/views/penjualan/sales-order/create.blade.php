@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Buat Sales Order</h4>
        </div>

        <form action="{{ route('penjualan.sales-order.store') }}" method="POST" id="soForm">
            @csrf

            @if ($errors->any())
    <div style="color:red;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. SO</label>
                        <input type="text" name="no" class="form-control"
                            value="{{ generateDocumentNumber('orders', 'PCS-SO', 'sales') }}" readonly
                            style="background-color: #e9ecef;">
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal SO</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customer as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>TOP (Hari)</label>
                        <input type="number" name="top" class="form-control" value="0">
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Kirim</label>
                        <input type="date" name="tgl_kirim" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <hr>
                <h5>Detail Barang</h5>
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="barang_id[]" class="form-control barang-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach(\App\Models\Barang::all() as $b)
                                        <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01" required>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="harga[]" class="form-control harga" min="0" step="0.01"
                                        readonly style="background-color:#e9ecef;">
                                    <button type="button" class="btn btn-outline-secondary edit-harga">
                                        ✏️
                                    </button>
                                </div>
                            </td>
                            <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly
                                    style="background-color:#e9ecef;"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <label>Subtotal (DPP)</label>
                        <input type="number" name="dpp" id="dpp" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Pajak 11%</label>
                        <input type="number" name="pajak" id="pajak" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Total</label>
                        <input type="number" name="total" id="total" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control" value="Belum Lunas" readonly
                            style="background-color:#e9ecef;">
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button class="btn btn-success">Simpan SO</button>
                <a href="{{ route('pembelian.purchase-order.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Fungsi hitung subtotal per baris
            function calculateRow(row) {
                let qty = parseFloat(row.querySelector('.qty').value) || 0;
                let harga = parseFloat(row.querySelector('.harga').value) || 0;
                let subtotalInput = row.querySelector('.subtotal-detail');

                let subtotal = qty * harga;
                subtotalInput.value = subtotal.toFixed(2);
                calculateTotal();
            }

            // Fungsi hitung total keseluruhan (DPP, Pajak, Grand Total)
            function calculateTotal() {
                let dpp = 0;
                document.querySelectorAll('.subtotal-detail').forEach(input => {
                    dpp += parseFloat(input.value) || 0;
                });

                let pajak = dpp * 0.11;
                let total = dpp + pajak;

                document.getElementById('dpp').value = dpp.toFixed(2);
                document.getElementById('pajak').value = pajak.toFixed(2);
                document.getElementById('total').value = total.toFixed(2);
            }

            // FUNGSI INI YANG PENTING: Ambil harga dari Server
            async function fetchHarga(row) {
                let barangId = row.querySelector('.barang-select').value;
                let qty = row.querySelector('.qty').value;
                let hargaInput = row.querySelector('.harga');

                if (barangId && qty > 0) {
                    try {
                        // Panggil route yang tadi dibuat
                        let response = await fetch(`{{ route('barang.get-harga') }}?barang_id=${barangId}&qty=${qty}`);
                        let data = await response.json();

                        hargaInput.value = data.harga;
                        calculateRow(row);
                    } catch (error) {
                        console.error("Gagal mengambil harga", error);
                    }
                }
            }

            // Event listener saat Barang atau Qty berubah
            document.getElementById('items-table').addEventListener('change', function (e) {
                if (e.target.classList.contains('barang-select') || e.target.classList.contains('qty')) {
                    let row = e.target.closest('tr');
                    fetchHarga(row); // Panggil fungsi fetchHarga
                }
            });

            // Event listener saat Qty atau Harga diketik manual (untuk subtotal instan)
            document.getElementById('items-table').addEventListener('input', function (e) {
                if (e.target.classList.contains('qty') || e.target.classList.contains('harga')) {
                    let row = e.target.closest('tr');
                    calculateRow(row);
                }
            });

            // Tombol tambah baris
            document.getElementById('add-row').addEventListener('click', function () {
                let tbody = document.querySelector('#items-table tbody');
                let firstRow = tbody.querySelector('tr');
                let newRow = firstRow.cloneNode(true);

                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    if (input.classList.contains('harga')) {
                        input.setAttribute('readonly', true);
                        input.style.backgroundColor = "#e9ecef";
                    }
                });
                newRow.querySelector('select').selectedIndex = 0;
                tbody.appendChild(newRow);
            });

            // Tombol hapus baris
            document.getElementById('items-table').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    let tbody = document.querySelector('#items-table tbody');
                    if (tbody.rows.length > 1) {
                        e.target.closest('tr').remove();
                        calculateTotal();
                    }
                }

                // Edit harga manual
                if (e.target.classList.contains('edit-harga')) {
                    let row = e.target.closest('tr');
                    let hargaInput = row.querySelector('.harga');
                    hargaInput.removeAttribute('readonly');
                    hargaInput.style.backgroundColor = "#fff";
                    hargaInput.focus();
                }
            });
        });
    </script>
@endsection