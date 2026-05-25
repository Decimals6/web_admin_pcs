@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Edit Purchase Order</h4>
        </div>

        <form action="{{ route('pembelian.purchase-order.update', $order->id) }}" method="POST" id="poForm">
            @csrf
            @method('PUT')
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. PO</label>
                        <input type="text" name="no" class="form-control" value="{{ $order->no }}">
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal PO</label>
                        <input type="date" name="tgl" class="form-control"
                            value="{{ \Carbon\Carbon::parse($order->tgl)->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Supplier</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ $order->supplier_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        // Memecah kembali nilai TOP dan Tgl Kirim dari field keterangan jika polanya konisten
                        // Pola: "TOP: X | Tgl Kirim: Y | Z"
                        preg_match('/TOP:\s*(\d+)/', $order->keterangan, $matchesTop);
                        preg_match('/Tgl Kirim:\s*([\d-]+)/', $order->keterangan, $matchesTgl);

                        $valTop = isset($matchesTop[1]) ? $matchesTop[1] : 0;
                        $valTglKirim = isset($matchesTgl[1]) ? $matchesTgl[1] : date('Y-m-d');

                        // Ambil sisa keterangan asli jika ada
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
                            <th style="width:50%">Barang</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:20%">Harga</th>
                            <th style="width:15%">Subtotal</th>
                            <th style="width:5%">Aksi</th>
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
                                <td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01"
                                        value="{{ $detail->qty }}" required></td>
                                <td><input type="number" name="harga[]" class="form-control harga" min="0" step="0.01"
                                        value="{{ $detail->harga }}" required></td>
                                <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail"
                                        value="{{ $detail->subtotal }}" readonly style="background-color:#e9ecef;"></td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

                <hr>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Gunakan PPN</label>
                        <select name="use_ppn" id="use_ppn" class="form-control">
                            <option value="1" {{ $order->pajak > 0 ? 'selected' : '' }}>Pakai PPN</option>
                            <option value="0" {{ $order->pajak == 0 ? 'selected' : '' }}>Tidak Pakai PPN</option>
                        </select>
                    </div>
                </div>
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
                <button class="btn btn-success">Perbarui PO</button>
                <a href="{{ route('pembelian.purchase-order.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            function initSelect2(element) {
                element.select2({
                    placeholder: "Cari barang",
                    width: '100%'
                });
            }

            // Langsung nyalakan select2 untuk semua row yang ke-load saat edit
            initSelect2($('.barang-select'));

            function calculateRow(row) {
                let qty = parseFloat($(row).find('.qty').val());
                let harga = parseFloat($(row).find('.harga').val());

                if (isNaN(qty)) qty = 0;
                if (isNaN(harga)) harga = 0;

                let subtotal = qty * harga;
                $(row).find('.subtotal-detail').val(subtotal.toFixed(2));

                calculateTotal();
            }

            function calculateTotal() {
                let dpp = 0;
                $('.subtotal-detail').each(function () {
                    let val = parseFloat($(this).val());
                    if (!isNaN(val)) {
                        dpp += val;
                    }
                });

                let usePpn = $('#use_ppn').val();
                let pajak = 0;
                if (usePpn == 1) {
                    pajak = dpp * 0.11;
                }

                let total = dpp + pajak;

                $('#dpp').val(dpp.toFixed(2));
                $('#pajak').val(pajak.toFixed(2));
                $('#total').val(total.toFixed(2));
            }

            // Fungsi tambah row aman pas edit
            $('#add-row').click(function () {
                let tbody = $('#items-table tbody');
                let firstRow = tbody.find('tr:first');

                // Destroy select2 baris pertama sebentar biar clonning-nya bersih gak error bug select2
                firstRow.find('.barang-select').select2('destroy');
                let newRow = firstRow.clone();

                // Balikin select2 baris pertama
                initSelect2(firstRow.find('.barang-select'));

                // Kosongkan value inputan baru
                newRow.find('input').val('');
                newRow.find('select').prop('selectedIndex', 0);

                tbody.append(newRow);

                // Jalankan select2 di baris baru
                initSelect2(newRow.find('.barang-select'));
            });

            $('#items-table').on('click', '.remove-row', function () {
                let tbody = $('#items-table tbody');
                if (tbody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                }
            });

            $('#items-table').on('input', '.qty, .harga', function () {
                let row = $(this).closest('tr');
                calculateRow(row);
            });

            $('#use_ppn').on('change', function () {
                calculateTotal();
            });
        });
    </script>
@endsection