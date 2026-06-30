@extends('layouts.admin')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Waduh, ada yang salah Do!</strong> Tolong cek inputanmu:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Gagal Potong Stok:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Buat Distribusi Sampel</h4>
        </div>

        <form action="{{ route('gudang.sampel.store') }}" method="POST" id="sampelForm">
            @csrf
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>Tanggal Keluar</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Pelanggan / Customer</label>
                        <select name="customer_id" class="form-control select2-customer" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label>Keterangan Umum</label>
                        <input type="text" name="keterangan" class="form-control"
                            placeholder="Contoh: Sampel untuk pameran / trial pabrik...">
                    </div>
                </div>

                <hr>
                <h5>Detail Item Barang Sampel</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="items-table">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th style="width: 75%">Barang</th>
                                <th style="width: 20%">Jumlah (Qty)</th>
                                <th style="width: 5%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="barang_id[]" class="form-control barang-select" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach($barangs as $b)
                                            <option value="{{ $b->id }}">{{ $b->nama_barang }} (Stok: {{ $b->stok }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="jumlah[]" class="form-control jumlah" min="1"
                                        placeholder="Pcs" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-row">-</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">Simpan & Potong Stok</button>
                <a href="{{ route('gudang.sampel.index') }}" class="btn btn-secondary">Kembali</a>
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

            // Init Select2 awal
            function initSelect2(element, placeholderText) {
                element.select2({
                    placeholder: placeholderText,
                    width: '100%'
                });
            }

            initSelect2($('.barang-select'), "Cari barang...");
            initSelect2($('.select2-customer'), "-- Pilih Customer --");

            /* ===============================
            TAMBAH ROW BARANG DYNAMIC
            =============================== */
            $('#add-row').click(function () {
                let tbody = $('#items-table tbody');
                let firstRow = tbody.find('tr:first');

                // Destroy select2 biar clone-nya ga ngebug id internalnya
                firstRow.find('.barang-select').select2('destroy');

                let newRow = firstRow.clone();

                // Reset isi value row baru
                newRow.find('input').val('');
                newRow.find('select').prop('selectedIndex', 0);

                tbody.append(newRow);

                // Re-init select2 untuk semua row
                initSelect2($('.barang-select'), "Cari barang...");
            });

            /* ===============================
            HAPUS ROW BARANG
            =============================== */
            $('#items-table').on('click', '.remove-row', function () {
                let tbody = $('#items-table tbody');
                if (tbody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                } else {
                    alert('Minimal harus ada 1 item barang sampel, Do!');
                }
            });

        });
    </script>
@endsection