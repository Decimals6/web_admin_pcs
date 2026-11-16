@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- KPI Ringkasan Stok Keseluruhan --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalItem) }}</h3>
                    <p>Total Jenis Barang</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <a href="{{ route('mutasi-barangs.index') }}" class="small-box-footer">
                    Lihat Semua Stok <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalStokFisik) }}</h3>
                    <p>Total Unit Stok Tersedia</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
                <a href="{{ route('mutasi-barangs.index', ['status_stok' => 'aman']) }}" class="small-box-footer">
                    Filter Stok Aman (&gt; 10) <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stokMenipisCount) }}</h3>
                    <p>Stok Menipis (1 - 10)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <a href="{{ route('mutasi-barangs.index', ['status_stok' => 'menipis']) }}" class="small-box-footer">
                    Filter Stok Menipis <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stokKosongCount) }}</h3>
                    <p>Stok Kosong (0)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <a href="{{ route('mutasi-barangs.index', ['status_stok' => 'kosong']) }}" class="small-box-footer">
                    Filter Stok Kosong <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Card Utama: Monitor Stok & Kartu Stok --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-2 mb-md-0">
                    <i class="fas fa-clipboard-list mr-1"></i>
                    {{ $selectedBarang ? 'Kartu Stok: ' . $selectedBarang->nama_barang : 'Monitor Stok Keseluruhan & Kartu Stok' }}
                </h3>
                <div>
                    @if($selectedBarang)
                        <a href="{{ route('mutasi-barangs.index') }}" class="btn btn-sm btn-secondary mr-1">
                            <i class="fas fa-th-list mr-1"></i> Semua Stok Barang
                        </a>
                    @endif
                    <a href="{{ route('mutasi-barangs.create', $barangId ? ['barang_id' => $barangId] : []) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus mr-1"></i> Penyesuaian Stok (Manual)
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- Filter Pemilih Barang & Periode/Sumber --}}
            <form method="GET" action="{{ route('mutasi-barangs.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-muted mb-1">Pilih Barang (Kartu Stok Detail)</label>
                        <select name="barang_id" class="form-control select-barang" onchange="this.form.submit()">
                            <option value="">-- Tampilkan Semua Stok Barang --</option>
                            @foreach($barangs as $barang)
                                <option value="{{ $barang->id }}"
                                        data-stok="{{ $barang->stok }}"
                                        {{ $barangId == $barang->id ? 'selected' : '' }}>
                                    {{ $barang->kode_barang }} - {{ $barang->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($selectedBarang)
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Sumber Transaksi</label>
                            <select name="sumber" class="form-control">
                                <option value="">Semua Sumber</option>
                                <option value="pembelian" {{ request('sumber') === 'pembelian' ? 'selected' : '' }}>Pembelian (SJ Masuk)</option>
                                <option value="penjualan" {{ request('sumber') === 'penjualan' ? 'selected' : '' }}>Penjualan (SJ Keluar)</option>
                                <option value="sampel" {{ request('sumber') === 'sampel' ? 'selected' : '' }}>Sampel</option>
                                <option value="manual" {{ request('sumber') === 'manual' ? 'selected' : '' }}>Manual / Opname</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-block mr-1">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            @if(request('from') || request('to') || request('sumber'))
                                <a href="{{ route('mutasi-barangs.index', ['barang_id' => $barangId]) }}" class="btn btn-default" title="Reset Filter">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Cari Kode / Nama Barang / Supplier</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Ketik nama barang, kode, atau supplier...">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Status Stok</label>
                            <select name="status_stok" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="aman" {{ request('status_stok') === 'aman' ? 'selected' : '' }}>Aman (&gt; 10)</option>
                                <option value="menipis" {{ request('status_stok') === 'menipis' ? 'selected' : '' }}>Menipis (1 - 10)</option>
                                <option value="kosong" {{ request('status_stok') === 'kosong' ? 'selected' : '' }}>Kosong (0)</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info btn-block mr-1">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            @if(request('search') || request('status_stok'))
                                <a href="{{ route('mutasi-barangs.index') }}" class="btn btn-default" title="Reset Pencarian">
                                    <i class="fas fa-undo"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </form>

            @if($selectedBarang)
                {{-- Ringkasan Info Barang Terpilih & Cek Rekonsiliasi Stok --}}
                @php
                    $selisihStok = $selectedBarang->stok - $saldoAkhirMutasi;
                @endphp

                <div class="callout callout-info mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <div class="text-muted small">Kode: <strong>{{ $selectedBarang->kode_barang }}</strong> | Supplier: <strong>{{ $selectedBarang->supplier->nama_supplier ?? '-' }}</strong></div>
                            <h5 class="mb-0 font-weight-bold">{{ $selectedBarang->nama_barang }}</h5>
                        </div>
                        <div class="col-md-7">
                            <div class="row text-center">
                                <div class="col-3">
                                    <small class="text-muted d-block">Masuk (Filter)</small>
                                    <span class="badge badge-success px-2 py-1" style="font-size: 0.9rem;">+{{ number_format($totalMasuk) }}</span>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block">Keluar (Filter)</small>
                                    <span class="badge badge-danger px-2 py-1" style="font-size: 0.9rem;">-{{ number_format($totalKeluar) }}</span>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block">Saldo Mutasi</small>
                                    <span class="font-weight-bold text-dark" style="font-size: 1.05rem;">{{ number_format($saldoAkhirMutasi) }}</span>
                                </div>
                                <div class="col-3">
                                    <small class="text-muted d-block">Stok Sistem</small>
                                    <span class="font-weight-bold text-primary" style="font-size: 1.05rem;">{{ number_format($selectedBarang->stok) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($selisihStok !== 0)
                        <div class="alert alert-warning mt-2 mb-0 py-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Perhatian Rekonsiliasi:</strong> Stok Sistem (<strong>{{ number_format($selectedBarang->stok) }}</strong>) berbeda sebesar <strong>{{ $selisihStok > 0 ? '+' . $selisihStok : $selisihStok }}</strong> dengan Saldo Akhir Mutasi (<strong>{{ number_format($saldoAkhirMutasi) }}</strong>) akibat histori transaksi lama sebelum sistem mutasi terkunci.
                        </div>
                    @endif
                </div>

                {{-- Tabel Riwayat Kartu Stok --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="12%">Tanggal</th>
                                <th width="14%" class="text-center">Sumber</th>
                                <th width="10%" class="text-right">Masuk</th>
                                <th width="10%" class="text-right">Keluar</th>
                                <th width="11%" class="text-right">Saldo</th>
                                <th>Keterangan / Referensi</th>
                                <th width="8%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mutasis as $mutasi)
                                <tr>
                                    <td class="text-center">{{ ($mutasis->currentPage() - 1) * $mutasis->perPage() + $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($mutasi->tgl_mutasi)->format('d-m-Y') }}</td>
                                    <td class="text-center">
                                        @switch($mutasi->sumber_tampil)
                                            @case('pembelian')
                                                <span class="badge badge-success"><i class="fas fa-truck-loading mr-1"></i>Pembelian</span>
                                                @break
                                            @case('penjualan')
                                                <span class="badge badge-primary"><i class="fas fa-shipping-fast mr-1"></i>Penjualan</span>
                                                @break
                                            @case('sampel')
                                                <span class="badge badge-warning"><i class="fas fa-gift mr-1"></i>Sampel</span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary"><i class="fas fa-sliders-h mr-1"></i>Manual</span>
                                        @endswitch
                                    </td>
                                    <td class="text-right text-success font-weight-bold">
                                        {{ $mutasi->masuk > 0 ? '+' . number_format($mutasi->masuk) : '-' }}
                                    </td>
                                    <td class="text-right text-danger font-weight-bold">
                                        {{ $mutasi->keluar > 0 ? '-' . number_format($mutasi->keluar) : '-' }}
                                    </td>
                                    <td class="text-right font-weight-bold">{{ number_format($mutasi->saldo) }}</td>
                                    <td>
                                        @if($mutasi->keterangan)
                                            {{ $mutasi->keterangan }}
                                        @elseif($mutasi->deliveryNoteDetail && $mutasi->deliveryNoteDetail->deliveryNote)
                                            SJ {{ $mutasi->deliveryNoteDetail->deliveryNote->no }}
                                        @elseif($mutasi->sampel)
                                            Sampel ke {{ $mutasi->sampel->customer->nama_customer ?? '-' }}
                                        @else
                                            <span class="text-muted font-italic">Tanpa keterangan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($mutasi->sumber_tampil === 'manual' && !$mutasi->delivery_note_detail_id && !$mutasi->sampel_id)
                                            <form action="{{ route('mutasi-barangs.destroy', $mutasi->id) }}" method="POST" onsubmit="return confirm('Hapus mutasi manual ini? Stok barang akan dikembalikan otomatis.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" title="Hapus Mutasi Manual">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small" title="Terkunci ke dokumen transaksi"><i class="fas fa-lock"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data mutasi untuk barang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <div class="text-muted small mb-2 mb-md-0">
                        Menampilkan {{ $mutasis->firstItem() ?? 0 }} - {{ $mutasis->lastItem() ?? 0 }} dari {{ $mutasis->total() }} transaksi mutasi
                    </div>
                    <div>{{ $mutasis->links('pagination::bootstrap-4') }}</div>
                </div>

            @else
                {{-- Mode Default: Tabel Stok Keseluruhan Semua Barang --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="15%">Kode Barang</th>
                                <th>Nama Barang</th>
                                <th width="22%">Supplier</th>
                                <th width="12%" class="text-right">Stok Saat Ini</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="14%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stokList as $item)
                                <tr>
                                    <td class="text-center">{{ ($stokList->currentPage() - 1) * $stokList->perPage() + $loop->iteration }}</td>
                                    <td><code>{{ $item->kode_barang }}</code></td>
                                    <td class="font-weight-bold">{{ $item->nama_barang }}</td>
                                    <td>{{ $item->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="text-right font-weight-bold" style="font-size: 1.05rem;">
                                        {{ number_format($item->stok) }}
                                    </td>
                                    <td class="text-center">
                                        @if($item->stok <= 0)
                                            <span class="badge badge-danger">Kosong</span>
                                        @elseif($item->stok <= 10)
                                            <span class="badge badge-warning">Menipis</span>
                                        @else
                                            <span class="badge badge-success">Aman</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('mutasi-barangs.index', ['barang_id' => $item->id]) }}" class="btn btn-xs btn-info">
                                            <i class="fas fa-history mr-1"></i> Kartu Stok
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada data barang yang sesuai filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                    <div class="text-muted small mb-2 mb-md-0">
                        Menampilkan {{ $stokList->firstItem() ?? 0 }} - {{ $stokList->lastItem() ?? 0 }} dari {{ $stokList->total() }} barang
                    </div>
                    <div>{{ $stokList->links('pagination::bootstrap-4') }}</div>
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
            var warna = '#dc3545';

            if (stok > 10) {
                warna = '#28a745';
            } else if (stok > 0) {
                warna = '#ffc107';
            }

            var bold = stok > 0 ? 'font-weight:bold;' : '';

            return $(
                '<div style="display:flex;justify-content:space-between;width:100%;">' +
                    '<span style="' + bold + '">' + text + '</span>' +
                    '<span style="color:' + warna + ';font-weight:bold;">' + stok + '</span>' +
                '</div>'
            );
        }

        $('.select-barang').select2({
            placeholder: "-- Tampilkan Semua Stok Barang --",
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
