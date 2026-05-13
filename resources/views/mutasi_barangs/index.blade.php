@extends('layouts.admin')

@section('content')
<div class="container">
        <div class="card">
                <div class="card-header">
                        <h4>Kartu Stok Barang</h4>
                </div>

                <div class="card-body">
                   <form method="GET">
                    <div class="row mb-4">
                         <div class="col-md-6">
                                <select name="barang_id"
                                        class="form-control select-barang"
                                        onchange="this.form.submit()">
                                <option value="">-- Pilih Barang --</option>
                                @foreach($barangs as $barang)
                                <option value="{{ $barang->id }}"
                                        data-stok="{{ $barang->stok }}"
                                        {{ $barangId == $barang->id ? 'selected' : '' }}>
                                        {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                                </option>
                                @endforeach
                                </select>
                        </div>
                   </div>
                 </form>

                @if($barangId)
                 <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                                <tr>
                                <th width="5%">No</th>
                                <th width="15%">Tanggal</th>
                                <th width="10%" class="text-end">Masuk</th>
                                <th width="10%" class="text-end">Keluar</th>
                                <th width="10%" class="text-end">Saldo</th>
                                <th>Keterangan</th>
                                </tr>
                        </thead>

                        <tbody>
                        @forelse($mutasis as $mutasi)

                        <tr>
                        <td>{{ ($mutasis->currentPage() - 1) * $mutasis->perPage() + $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($mutasi->tgl_mutasi)->format('d-m-Y') }}</td>
                        <td class="text-end text-success">{{ $mutasi->masuk }}</td>
                        <td class="text-end text-danger">{{ $mutasi->keluar }}</td>
                        <td class="text-end fw-bold">{{ $mutasi->saldo }}</td>
                        <td>{{ $mutasi->keterangan }}</td>
                        </tr>

                        @empty
                        <tr>
                        <td colspan="6" class="text-center">Belum ada mutasi</td>
                        </tr>

                        @endforelse
                        </tbody>
                </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Menampilkan {{ $mutasis->firstItem() ?? 0 }}-{{ $mutasis->lastItem() ?? 0 }}
                        dari {{ $mutasis->total() }} data
                </div>
        <div>{{ $mutasis->links('pagination::bootstrap-5') }}</div>
        </div>

        @else
        <div class="alert alert-info">
                Silakan pilih barang untuk melihat kartu stok
        </div>
        @endif

        </div>
     </div>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function () {

    if ($('.select-barang').length) {

        function formatBarang(option) {

            if (!option.id) {
                return option.text;
            }

            var stok = $(option.element).data('stok') || 0;
            var text = option.text;

            var warna = '#dc3545'; // merah

            if (stok > 50) {
                warna = '#28a745'; // hijau
            } 
            else if (stok > 0) {
                warna = '#ffc107'; // kuning
            }

            var bold = stok > 0 ? 'font-weight:bold;' : '';

            var template =
                '<div style="display:flex;justify-content:space-between;width:100%;">' +
                    '<span style="'+bold+'">' + text + '</span>' +
                    '<span style="color:'+warna+';font-weight:bold;">' + stok + '</span>' +
                '</div>';

            return $(template);
        }

        $('.select-barang').select2({
            placeholder: "-- Pilih Barang --",
            allowClear: true,
            width: '100%',
            templateResult: formatBarang,
            templateSelection: function (option) {
                return option.text || option.id;
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });

    }

});

</script>
@endsection