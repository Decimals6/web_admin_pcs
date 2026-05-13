@extends('layouts.admin')

@section('content')
    <div class="container-fluid">

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit"></i> Input Absensi Baru</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('absensi.absen-karyawan.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"
                                required>
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"
                                required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Buka Form Checkbox</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($period))
            <form action="{{ route('absensi.absen-karyawan.store') }}" method="POST" id="formAbsensi">
                @csrf
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                <div class="card">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-bordered text-nowrap">
                            <thead class="bg-dark">
                                <tr>
                                    <th class="align-middle">Nama Karyawan</th>
                                    @foreach($period as $date)
                                        <th class="text-center">{{ $date->format('d/m') }}</th>
                                    @endforeach
                                    <th class="text-center align-middle bg-primary">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        @foreach($period as $date)
                                            <td class="text-center">
                                                <input type="checkbox" name="kehadiran[{{ $user->id }}][]"
                                                    value="{{ $date->format('Y-m-d') }}" class="cb-hadir" data-userid="{{ $user->id }}">
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span id="count-{{ $user->id }}" class="font-weight-bold">0</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success float-right">Simpan Absensi & Hitung Premi</button>
                    </div>
                </div>
            </form>
        @endif

        <div class="card mt-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Riwayat Sesi Absensi</h3>
                    <button class="btn btn-success btn-sm" id="btnPrint">
                        <i class="fas fa-print"></i> Print Absensi
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $h)
                            <tr>
                                <td>{{ $h->tanggal_mulai }} s/d {{ $h->tanggal_akhir }}</td>
                                <td>{{ $h->keterangan }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm btn-detail" data-id="{{ $h->id }}">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white">Detail Absensi & Premi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="modalBody" class="modal-body">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="modalPrint" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">Print Absensi</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <!-- Pilih bulan -->
                        <div class="form-group">
                            <label>Bulan</label>
                            <input type="month" id="bulan" class="form-control">
                        </div>

                        <!-- Pilih karyawan -->

                        <div class="form-group">
                            <label>Karyawan</label>
                            @foreach($allUsers as $user)
                                <div>
                                    <input type="checkbox" class="cb-user-print" value="{{ $user->id }}">
                                    {{ $user->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" id="btnDoPrint">Print</button>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            console.log("JS Absensi Ready!");

            // GLOBAL AJAX SETUP: Penting untuk menangani CSRF pada semua request AJAX (POST)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. HITUNG CHECKBOX SECARA REALTIME
            $(document).on('change', '.cb-hadir', function () {
                let userId = $(this).data('userid');
                let totalHadir = $('.cb-hadir[data-userid="' + userId + '"]:checked').length;
                $('#count-' + userId).text(totalHadir);
            });

            // 2. LIHAT DETAIL (AJAX GET)
            $(document).on('click', '.btn-detail', function (e) {
                e.preventDefault();
                let id = $(this).data('id');

                $('#modalDetail').modal('show');
                $('#modalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Memuat data...</p></div>');

                $.get("{{ url('absensi/absen-karyawan/detail') }}/" + id, function (response) {
                    $('#modalBody').html(response);
                }).fail(function () {
                    $('#modalBody').html('<div class="alert alert-danger">Gagal mengambil data.</div>');
                });
            });

            // 3. SIMPAN ABSENSI (AJAX POST)
            $('#formAbsensi').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let url = form.attr('action');
                let data = form.serialize();

                $.post(url, data, function (res) {
                    if (res.status === 'warning') {
                        let msg = res.message + "\n";
                        res.users.forEach(u => {
                            msg += `- ${u.name}: Premi ${u.nominal_premi ?? 'null'}, Sewa ${u.nominal_sewa ?? 'null'}\n`;
                        });

                        if (confirm(msg + "\nTetap lanjut simpan?")) {
                            $.post(url, data + '&force=1', function (res2) {
                                alert(res2.message);
                                if (res2.status === 'success') {
                                    // REDIRECT KE HALAMAN BERSIH (RESET)
                                    window.location.href = "{{ route('absensi.absen-karyawan.index') }}";
                                }
                            });
                        }
                    } else {
                        alert(res.message);
                        if (res.status === 'success') {
                            // REDIRECT KE HALAMAN BERSIH (RESET)
                            window.location.href = "{{ route('absensi.absen-karyawan.index') }}";
                        }
                    }
                }).fail(function (xhr) {
                    let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan server.";
                    alert("Error: " + errorMsg);
                });
            });
        });
        
        $(document).ready(function () {

            $('#btnPrint').on('click', function () {
                $('#modalPrint').modal('show');
            });

            $('#btnDoPrint').on('click', function () {
                let bulan = $('#bulan').val();

                let users = [];
                $('.cb-user-print:checked').each(function () {
                    users.push($(this).val());
                });

                if (!bulan || users.length === 0) {
                    alert("Pilih bulan dan minimal 1 karyawan");
                    return;
                }

                // Ganti baris ini di JS kamu:
                let url = "{{ route('absensi.print') }}?bulan=" + bulan + "&users=" + users.join(',');

                window.open(url, '_blank');
            });

        });
    </script>
@endsection
