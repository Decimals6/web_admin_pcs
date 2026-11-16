@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Buat Sales Order</h4>
        </div>

        <form action="{{ route('penjualan.sales-order.store') }}" method="POST" id="soForm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. SO</label>
                        <div class="input-group">
                            <input type="text" name="no" id="no_so" class="form-control"
                                value="{{ generateDocumentNumber('orders', 'PCS-SO', 'sales') }}" readonly
                                style="background-color: #e9ecef;">

                            <button class="btn btn-outline-secondary" type="button" id="btn-toggle-so"
                                title="Edit Nomor SO">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal SO</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control select2-search" data-placeholder="-- Cari / Pilih Customer --" required>
                            <option value="">-- Cari / Pilih Customer --</option>
                            @foreach($customer as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->kode_customer ? '[' . $s->kode_customer . '] ' : '' }}{{ $s->nama_customer }}
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
                            <th style="width:45%">Barang</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:20%">Harga</th>
                            <th style="width:18%">Subtotal</th>
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
                                            [{{ $b->kode_barang }}] {{ $b->nama_barang }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="qty[]" class="form-control qty" min="0.01" step="0.01" required></td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="harga[]" class="form-control harga" min="0" step="0.01"
                                        readonly style="background-color:#e9ecef;">
                                    <button type="button" class="btn btn-outline-secondary edit-harga" title="Edit Harga Manual">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </div>
                            </td>
                            <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly
                                    style="background-color:#e9ecef;"></td>
                            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
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
                <a href="{{ route('penjualan.sales-order.index') }}" class="btn btn-secondary">Kembali</a>
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
                    <option value="{{ $b->id }}" data-stok="{{ $b->stok }}">[{{ $b->kode_barang }}] {{ addslashes($b->nama_barang) }}</option>
                @endforeach
            `;

            $('.select2-search').select2({
                placeholder: function () {
                    return $(this).data('placeholder') || '-- Pilih --';
                },
                allowClear: true,
                width: '100%'
            });

            function formatBarangOption(option) {
                if (!option.id) return option.text;
                let stok = parseFloat($(option.element).data('stok')) || 0;
                let badgeColor = stok > 10 ? '#28a745' : (stok > 0 ? '#d39e00' : '#dc3545');
                return $(`
                    <div style="display:flex;justify-content:space-between;align-items:center;width:100%;">
                        <span>${option.text}</span>
                        <span style="color:${badgeColor};font-weight:bold;font-size:0.85em;">Stok: ${stok}</span>
                    </div>
                `);
            }

            function initBarangSelect2(element) {
                element.select2({
                    placeholder: "-- Cari Kode / Nama Barang --",
                    allowClear: true,
                    width: '100%',
                    templateResult: formatBarangOption,
                    templateSelection: function (option) {
                        return option.text || option.id;
                    }
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

                let pajak = dpp * 0.11;
                let total = dpp + pajak;

                $('#dpp').val(dpp.toFixed(2));
                $('#pajak').val(pajak.toFixed(2));
                $('#total').val(total.toFixed(2));
            }

            async function fetchHarga(row) {
                let barangId = $(row).find('.barang-select').val();
                let qty = $(row).find('.qty').val();
                let hargaInput = $(row).find('.harga');

                if (barangId && qty > 0) {
                    try {
                        let response = await fetch(`{{ route('barang.get-harga') }}?barang_id=${barangId}&qty=${qty}`);
                        let data = await response.json();
                        hargaInput.val(data.harga);
                        calculateRow(row);
                    } catch (error) {
                        console.error("Gagal mengambil harga", error);
                    }
                }
            }

            $('#items-table').on('change', '.barang-select, .qty', function () {
                let row = $(this).closest('tr');
                fetchHarga(row);
            });

            $('#items-table').on('input', '.qty, .harga', function () {
                calculateRow($(this).closest('tr'));
            });

            $('#add-row').on('click', function () {
                let tbody = $('#items-table tbody');
                let newRow = $(`
                    <tr>
                        <td>
                            <select name="barang_id[]" class="form-control barang-select" required>
                                ${barangOptionsHtml}
                            </select>
                        </td>
                        <td><input type="number" name="qty[]" class="form-control qty" min="0.01" step="0.01" required></td>
                        <td>
                            <div class="input-group">
                                <input type="number" name="harga[]" class="form-control harga" min="0" step="0.01"
                                    readonly style="background-color:#e9ecef;">
                                <button type="button" class="btn btn-outline-secondary edit-harga" title="Edit Harga Manual">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            </div>
                        </td>
                        <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly
                                style="background-color:#e9ecef;"></td>
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

            $('#items-table').on('click', '.edit-harga', function () {
                let row = $(this).closest('tr');
                let hargaInput = row.find('.harga');
                hargaInput.removeAttr('readonly').css('background-color', '#fff').focus();
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
