@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Buat Purchase Order</h4>
        </div>

        <form action="{{ route('pembelian.purchase-order.store') }}" method="POST" id="poForm">
            @csrf
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. PO</label>
                        <input type="text" name="no" class="form-control" placeholder="Masukkan No. PO" required>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal PO</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Supplier</label>
                        <select name="supplier_id" id="supplier_id" class="form-control select2-search" data-placeholder="-- Cari / Pilih Supplier --" required>
                            <option value="">-- Cari / Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->nama_supplier }}{{ $s->telepon ? ' (' . $s->telepon . ')' : '' }}
                                </option>
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
                            <th style="width:50%">Barang</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:18%">Harga</th>
                            <th style="width:15%">Subtotal</th>
                            <th style="width:5%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="barang_id[]" class="form-control barang-select" required>
                                    <option value="">-- Cari Kode / Nama Barang --</option>
                                    @foreach($barangs as $b)
                                        <option value="{{ $b->id }}" data-stok="{{ $b->stok }}">
                                            [{{ $b->kode_barang }}] {{ $b->nama_barang }} (Stok: {{ $b->stok }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="qty[]" class="form-control qty" min="0.01" step="0.01" required></td>
                            <td><input type="number" name="harga[]" class="form-control harga" min="0" step="0.01" required></td>
                            <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly style="background-color:#e9ecef;"></td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

                <hr>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Gunakan PPN</label>
                        <select name="use_ppn" id="use_ppn" class="form-control">
                            <option value="1" selected>Pakai PPN</option>
                            <option value="0">Tidak Pakai PPN</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label>Subtotal (DPP)</label>
                        <input type="number" name="dpp" id="dpp" class="form-control" readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Pajak 11%</label>
                        <input type="number" name="pajak" id="pajak" class="form-control" readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Total</label>
                        <input type="number" name="total" id="total" class="form-control" readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control" value="Belum Lunas" readonly style="background-color:#e9ecef;">
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button class="btn btn-success">Simpan PO</button>
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
        $(document).ready(function () {
            const barangOptionsHtml = `
                <option value="">-- Cari Kode / Nama Barang --</option>
                @foreach($barangs as $b)
                    <option value="{{ $b->id }}" data-stok="{{ $b->stok }}">[{{ $b->kode_barang }}] {{ addslashes($b->nama_barang) }} (Stok: {{ $b->stok }})</option>
                @endforeach
            `;

            $('.select2-search').select2({
                placeholder: function () {
                    return $(this).data('placeholder') || '-- Pilih --';
                },
                allowClear: true,
                width: '100%'
            });

            function initBarangSelect2(element) {
                element.select2({
                    placeholder: "-- Cari Kode / Nama Barang --",
                    allowClear: true,
                    width: '100%'
                });
            }

            initBarangSelect2($('.barang-select'));

            function calculateRow(row) {
                let qty = parseFloat($(row).find('.qty').val()) || 0;
                let harga = parseFloat($(row).find('.harga').val()) || 0;
                let subtotal = qty * harga;
                $(row).find('.subtotal-detail').val(subtotal.toFixed(2));
                calculateTotal();
            }

            function calculateTotal() {
                let dpp = 0;
                $('.subtotal-detail').each(function () {
                    let val = parseFloat($(this).val());
                    if (!isNaN(val)) dpp += val;
                });

                let usePpn = $('#use_ppn').val();
                let pajak = usePpn == 1 ? dpp * 0.11 : 0;
                let total = dpp + pajak;

                $('#dpp').val(dpp.toFixed(2));
                $('#pajak').val(pajak.toFixed(2));
                $('#total').val(total.toFixed(2));
            }

            $('#add-row').click(function () {
                let tbody = $('#items-table tbody');
                let newRow = $(`
                    <tr>
                        <td>
                            <select name="barang_id[]" class="form-control barang-select" required>
                                ${barangOptionsHtml}
                            </select>
                        </td>
                        <td><input type="number" name="qty[]" class="form-control qty" min="0.01" step="0.01" required></td>
                        <td><input type="number" name="harga[]" class="form-control harga" min="0" step="0.01" required></td>
                        <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly style="background-color:#e9ecef;"></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                    </tr>
                `);
                tbody.append(newRow);
                initBarangSelect2(newRow.find('.barang-select'));
            });

            $('#items-table').on('click', '.remove-row', function () {
                let tbody = $('#items-table tbody');
                if (tbody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                }
            });

            $('#items-table').on('input', '.qty, .harga', function () {
                calculateRow($(this).closest('tr'));
            });

            $('#use_ppn').on('change', function () {
                calculateTotal();
            });
        });
    </script>
@endsection
