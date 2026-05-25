@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Edit Sales Order</h4>
        </div>

        <form action="{{ route('penjualan.sales-order.update', $order->id) }}" method="POST" id="soForm">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. SO</label>
                        <div class="input-group">
                            <input type="text" name="no" id="no_so" class="form-control" value="{{ $order->no }}" readonly
                                style="background-color: #e9ecef;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-toggle-so"
                                title="Edit Nomor SO">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal SO</label>
                        <input type="date" name="tgl" class="form-control"
                            value="{{ \Carbon\Carbon::parse($order->tgl)->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customer as $s)
                                <option value="{{ $s->id }}" {{ $order->customer_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_customer }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        // Pecah data TOP, Tgl Kirim, dan Keterangan asli dari database
                        preg_match('/TOP:\s*(\d+)/', $order->keterangan, $matchesTop);
                        preg_match('/Tgl Kirim:\s*([\d-]+)/', $order->keterangan, $matchesTgl);

                        $valTop = isset($matchesTop[1]) ? $matchesTop[1] : 0;
                        $valTglKirim = isset($matchesTgl[1]) ? $matchesTgl[1] : date('Y-m-d');

                        $parts = explode(' | ', $order->keterangan);
                        $valKet = isset($parts[2]) ? $parts[2] : '';
                    @endphp

                    <div class="col-md-2">
                        <label>TOP (Hari)</label>
                        <input type="number" name="top" class="form-control" value="{{ $valTop }}">
                    </div>
                    <div class="col-md-2">
                        <label>Tanggal Kirim</label>
                        <input type="date" name="tgl_kirim" class="form-control" value="{{ $valTglKirim }}" required>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-12">
                        <label>Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ $valKet }}</textarea>
                    </div>
                </div>

                <hr>
                <h5>Detail Barang</h5>
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th style="width:15%">Qty</th>
                            <th style="width:25%">Harga</th>
                            <th style="width:20%">Subtotal</th>
                            <th style="width:8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->details as $detail)
                            <tr>
                                <td>
                                    <select name="barang_id[]" class="form-control barang-select" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach($barangs as $b)
                                            <option value="{{ $b->id }}" {{ $detail->barang_id == $b->id ? 'selected' : '' }}>
                                                {{ $b->nama_barang }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="qty[]" class="form-control qty"
                                        value="{{ intval($detail->qty) }}" min="1" step="1" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="harga[]" class="form-control harga"
                                            value="{{ $detail->harga }}" min="0" step="0.01" readonly
                                            style="background-color:#e9ecef;">
                                        <button type="button" class="btn btn-outline-secondary edit-harga">✏️</button>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" name="subtotal_detail[]" class="form-control subtotal-detail"
                                        value="{{ $detail->subtotal }}" readonly style="background-color:#e9ecef;">
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <label>Subtotal (DPP)</label>
                        <input type="number" name="dpp" id="dpp" class="form-control" value="{{ $order->dpp }}" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Pajak 11%</label>
                        <input type="number" name="pajak" id="pajak" class="form-control" value="{{ $order->pajak }}"
                            readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Total</label>
                        <input type="number" name="total" id="total" class="form-control" value="{{ $order->total }}"
                            readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control" value="{{ $order->status }}" readonly
                            style="background-color:#e9ecef;">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-success">Perbarui SO</button>
                <a href="{{ route('penjualan.sales-order.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            function calculateRow(row) {
                let qty = parseInt(row.querySelector('.qty').value) || 0;
                let harga = parseFloat(row.querySelector('.harga').value) || 0;
                let subtotalInput = row.querySelector('.subtotal-detail');

                let subtotal = qty * harga;
                subtotalInput.value = subtotal.toFixed(2);
                calculateTotal();
            }

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

            async function fetchHarga(row) {
                let barangId = row.querySelector('.barang-select').value;
                let qty = row.querySelector('.qty').value;
                let hargaInput = row.querySelector('.harga');

                if (barangId && qty > 0) {
                    try {
                        let response = await fetch(`{{ route('barang.get-harga') }}?barang_id=${barangId}&qty=${qty}`);
                        let data = await response.json();

                        hargaInput.value = data.harga;
                        calculateRow(row);
                    } catch (error) {
                        console.error("Gagal mengambil harga", error);
                    }
                }
            }

            document.getElementById('items-table').addEventListener('change', function (e) {
                if (e.target.classList.contains('barang-select') || e.target.classList.contains('qty')) {
                    let row = e.target.closest('tr');
                    fetchHarga(row);
                }
            });

            document.getElementById('items-table').addEventListener('input', function (e) {
                if (e.target.classList.contains('qty') || e.target.classList.contains('harga')) {
                    let row = e.target.closest('tr');
                    calculateRow(row);
                }
            });

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
                    // Pastikan baris baru tetep kekunci step bulat
                    if (input.classList.contains('qty')) {
                        input.setAttribute('step', '1');
                        input.setAttribute('min', '1');
                    }
                });
                newRow.querySelector('select').selectedIndex = 0;
                tbody.appendChild(newRow);
            });

            document.getElementById('items-table').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    let tbody = document.querySelector('#items-table tbody');
                    if (tbody.rows.length > 1) {
                        e.target.closest('tr').remove();
                        calculateTotal();
                    }
                }

                if (e.target.classList.contains('edit-harga')) {
                    let row = e.target.closest('tr');
                    let hargaInput = row.querySelector('.harga');
                    hargaInput.removeAttribute('readonly');
                    hargaInput.style.backgroundColor = "#fff";
                    hargaInput.focus();
                }
            });

            const inputSO = document.getElementById("no_so");
            const btnToggle = document.getElementById("btn-toggle-so");

            btnToggle.addEventListener("click", function () {
                if (inputSO.hasAttribute("readonly")) {
                    inputSO.removeAttribute("readonly");
                    inputSO.style.backgroundColor = "#fff";
                    btnToggle.classList.remove("btn-outline-secondary");
                    btnToggle.classList.add("btn-danger");
                    btnToggle.innerHTML = '<i class="fas fa-lock"></i>';
                    inputSO.focus();
                } else {
                    inputSO.setAttribute("readonly", true);
                    inputSO.style.backgroundColor = "#e9ecef";
                    btnToggle.classList.remove("btn-danger");
                    btnToggle.classList.add("btn-outline-secondary");
                    btnToggle.innerHTML = '<i class="fas fa-pencil-alt"></i>';
                }
            });
        });
    </script>
@endsection