@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white align-items-center">
            <div class="d-flex justify-content-between">
                <h4>Log Distribusi Sampel Barang</h4>
                <a href="{{ route('gudang.sampel.create') }}" class="btn btn-success btn-sm">
                    + Buat Transaksi Sampel
                </a>
            </div>
        </div>

        <div class="card-body">
            {{-- SEARCH FORM --}}
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari nama pelanggan / keterangan sampel..." value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sampel-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Tanggal Distribusi</th>
                            <th>Pelanggan / Customer</th>
                            <th>Keterangan</th>
                            <th style="width:150px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($sampels as $s)
                            <tr>
                                <td>
                                    {{ ($sampels->currentPage() - 1) * $sampels->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($s->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $s->customer->nama_customer ?? '-' }}</td>
                                <td>{{ $s->keterangan ?? '-' }}</td>
                                <td class="text-center">
                                    {{-- Tombol Detail AJAX --}}
                                    <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $s->id }}">
                                        Detail
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('gudang.sampel.edit', $s->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Data penyerahan sampel belum tersedia</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $sampels->firstItem() ?? 0 }} – {{ $sampels->lastItem() ?? 0 }} dari
                    {{ $sampels->total() }} data
                </div>
                <div>
                    {{ $sampels->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Item Sampel Terdistribusi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    {{-- Konten diisi otomatis oleh AJAX --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Gunakan delegasi event agar link ajax tetap aktif sewaktu pindah page pagination
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));

                // Set loader spinner pas nunggu data ditarik
                $('#detailContent').html(`
                        <div class="text-center my-3">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Mengambil data detail barang...</p>
                        </div>
                    `);
                modal.show();

                // AJAX request mengambil potongan HTML rows tabel pivot
                $.get('/gudang/sampel/' + id + '/html-detail', function (htmlResult) {
                    $('#detailContent').html(htmlResult);
                }).fail(function () {
                    $('#detailContent').html('<div class="alert alert-danger m-2">Gagal memuat detail item barang. Pastikan route valid.</div>');
                });
            });
        });
    </script>
@endsection