@extends('layouts.admin')

@section('content')
    <div class="card">

        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4>Penawaran</h4>
            <a href="{{ route('penjualan.penawaran.create') }}" class="btn btn-success btn-sm">
                + Buat Penawaran
            </a>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- SEARCH --}}
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari No. Penawaran / Customer..."
                        value="{{ request('search') }}">
                    <button class="btn btn-secondary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>No. Penawaran</th>
                            <th>Tanggal</th>
                            <th>Berlaku Hingga</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th style="width:140px;">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($penawaran as $p)
                            <tr>
                                <td>
                                    {{ ($penawaran->currentPage() - 1) * $penawaran->perPage() + $loop->iteration }}
                                </td>

                                <td>{{ $p->no_penawaran }}</td>

                                <td>{{ $p->tanggal->format('d-m-Y') }}</td>

                                <td>
                                    {{ $p->berlaku_hingga ? $p->berlaku_hingga->format('d-m-Y') : '-' }}
                                    @if($p->isExpired())
                                        <span class="badge bg-danger">Kadaluarsa</span>
                                    @endif
                                </td>

                                <td>{{ $p->customer?->nama_customer ?? '-' }}</td>

                                <td>
                                    @php
                                        $badgeClass = match($p->status) {
                                            'draft' => 'bg-secondary',
                                            'terkirim' => 'bg-info',
                                            'diterima' => 'bg-success',
                                            'ditolak' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($p->status) }}</span>
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-primary btn-detail" data-id="{{ $p->id }}">
                                        Detail
                                    </button>

                                    <a href="{{ route('penjualan.penawaran.edit', $p->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    <form action="{{ route('penjualan.penawaran.destroy', $p->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Yakin hapus penawaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Data Penawaran belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Menampilkan {{ $penawaran->firstItem() ?? 0 }}
                    –
                    {{ $penawaran->lastItem() ?? 0 }}
                    dari {{ $penawaran->total() }} data
                </div>

                <div>
                    {{ $penawaran->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Penawaran</h5>
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
            $(document).on('click', '.btn-detail', function () {
                let id = $(this).data('id');
                let modal = new bootstrap.Modal(document.getElementById('detailModal'));

                $('#detailContent').html(`
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading...</p>
                    </div>
                `);
                modal.show();

                $.get('/penawaran/' + id, function (data) {
                    $('#detailContent').html(data);
                }).fail(function () {
                    $('#detailContent').html('<div class="alert alert-danger">Gagal mengambil data. Pastikan Route sudah benar.</div>');
                });
            });
        });
    </script>
@endsection