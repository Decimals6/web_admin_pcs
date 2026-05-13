@extends('layouts.admin')

@section('content')
    <div class="container-fluid">


        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Periode</th>
                        <th class="text-right">Total Premi</th>
                        <th class="text-right">Total Sewa</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-center">Jumlah Karyawan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensis as $a)

                        @php
                            $totalPremi = $a->premiHadirs->sum('subtotal_premi');
                            $totalSewa = $a->premiHadirs->sum('subtotal_sewa');
                            $grandTotal = $a->premiHadirs->sum('total_keseluruhan');
                            $totalUser = $a->premiHadirs->count();
                        @endphp

                        <tr>
                            <td>
                                {{ $a->tanggal_mulai }} s/d {{ $a->tanggal_akhir }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format($totalPremi, 0, ',', '.') }}
                            </td>

                            <td class="text-right">
                                Rp {{ number_format($totalSewa, 0, ',', '.') }}
                            </td>

                            <td class="text-right font-weight-bold text-primary">
                                Rp {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>

                            <td class="text-center">
                                {{ $totalUser }} Orang
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-info btn-detail" data-id="{{ $a->id }}">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                Belum ada data premi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $absensis->links() }}
            </div>
        </div>
    </div>
@endsection

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">Detail Premi per Karyawan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div id="modalBody" class="modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function () {

            $(document).on('click', '.btn-detail', function (e) {
                e.preventDefault();

                let id = $(this).data('id');

                $('#modalDetail').modal('show');

                $('#modalBody').html(`
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary"></div>
                                        <p class="mt-2">Memuat data...</p>
                                    </div>
                                `);

                $.ajax({
                    url: "{{ url('absensi/premi-hadir/detail') }}/" + id,
                    type: "GET",
                    success: function (response) {
                        $('#modalBody').html(response);
                    },
                    error: function (xhr) {
                        $('#modalBody').html(
                            '<div class="alert alert-danger">Gagal mengambil data.</div>'
                        );
                        console.error(xhr.responseText);
                    }
                });
            });

        });
    </script>
@endsection