@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Voucher Reimburse Petty Cash</h4>
            <a href="{{ route('petty_cash.voucher.create') }}" class="btn btn-primary">
                + Buat Voucher
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark bg-dark text-white">
                    <tr>
                        <th style="width: 70px;">No</th>
                        <th>No Voucher</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Akhir</th>
                        <th class="text-end">Total</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            {{-- Nomor urut menyesuaikan halaman pagination --}}
                            <td>
                                {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                            </td>
                            <td>{{ $row->no }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_mulai)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tgl_akhir)->format('d-m-Y') }}</td>

                            <td class="text-end fw-bold">
                                {{ number_format($row->total, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-info text-white btn-detail" data-id="{{ $row->id }}">
                                        Detail
                                    </button>

                                    <form action="{{ route('petty_cash.voucher.destroy', $row->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus voucher ini? Transaksi kas di dalamnya akan dilepas kembali.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada voucher</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ================= BAGIAN PAGINATION (YANG DITAMBAHKAN) ================= --}}
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Menampilkan {{ $data->firstItem() ?? 0 }}
                –
                {{ $data->lastItem() ?? 0 }}
                dari {{ $data->total() }} data
            </div>

            <div>
                {{-- Memunculkan link/tombol halaman Bootstrap --}}
                {{ $data->links('pagination::bootstrap-5') }}
            </div>
        </div>
        {{-- ======================================================================= --}}

    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detailContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailModalEl = document.getElementById('detailModal');
            const detailModal = new bootstrap.Modal(detailModalEl);

            document.querySelectorAll('.btn-detail').forEach(btn => {
                btn.addEventListener('click', function () {
                    let id = this.dataset.id;

                    document.getElementById('detailContent').innerHTML = `
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Loading...</p>
                            </div>
                        `;

                    detailModal.show();

                    fetch(`/petty_cash/voucher/${id}/detail`)
                        .then(res => res.text())
                        .then(data => {
                            document.getElementById('detailContent').innerHTML = data;
                        })
                        .catch(err => {
                            console.error(err);
                            document.getElementById('detailContent').innerHTML =
                                '<div class="alert alert-danger">Gagal memuat detail voucher.</div>';
                        });
                });
            });
        });
    </script>
@endsection