@extends('layouts.admin')

@section('content')
    <div class="card">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4>Sales Orders</h4>
            <a href="{{ route('penjualan.sales-order.create') }}" class="btn btn-success btn-sm">
                + Buat SO
            </a>
        </div>

        <div class="card-body">

            {{-- SEARCH --}}
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari No SO / Customer..."
                        value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>No. SO</th>
                            <th>Tanggal SO</th>
                            <th>Customer</th>
                            <th class="text-end">Nominal</th>
                            <th>Keterangan</th>
                            <th style="width:120px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $so)
                            <tr>
                                <td>
                                    {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                                </td>

                                <td>{{ $so->no }}</td>

                                <td>{{ $so->tgl->format('d-m-Y') }}</td>

                                <td>{{ $so->customer?->nama_customer ?? '-' }}</td>

                                <td class="text-end">
                                    {{ number_format($so->dpp, 0, ',', '.') }}
                                </td>

                                <td>{{ $so->keterangan ?? '-' }}</td>

                                <td>
                                    {{-- Tombol Detail - Tanpa Collapse, Pakai Class btn-detail --}}
                                    <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $so->id }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                            
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Data SO belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- INFO + PAGINATION --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $orders->firstItem() ?? 0 }}
                    –
                    {{ $orders->lastItem() ?? 0 }}
                    dari {{ $orders->total() }} data
                </div>

                <div>
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Sales Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Mengambil data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Gunakan delegasi event agar tombol tetap jalan meski ada pagination/search
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));

                // Tampilkan loading saat proses fetch
                $('#detailContent').html(`
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Loading...</p>
                            </div>
                        `);
                modal.show();

                // AJAX Request (Pastikan route ini sudah ada di web.php)
                // Saya sesuaikan dengan route fetch di script lama kamu: /po/{id}
                $.get('/po/' + id, function (data) {
                    $('#detailContent').html(data);
                }).fail(function () {
                    $('#detailContent').html('<div class="alert alert-danger">Gagal mengambil data. Pastikan Route sudah benar.</div>');
                });
            });
        });
    </script>
@endsection