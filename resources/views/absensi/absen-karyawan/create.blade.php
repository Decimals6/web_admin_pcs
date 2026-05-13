@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>Input Absensi Karyawan</h2>
            <form method="GET" action="{{ route('absensi.absen-karyawan.index') }}" class="row mt-4">
                <div class="col-md-4">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" required>
                </div>
                <div class="col-md-4">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary btn-block">Generate Tabel</button>
                </div>
            </form>
        </div>

        @if(request('start_date') && request('end_date'))
            <form action="{{ route('absensi.absen-karyawan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th style="vertical-align: middle; min-width: 150px;">Nama Karyawan</th>
                                    @foreach($period as $date)
                                        <th>{{ $date->format('d/m') }}<br><small>{{ $date->format('D') }}</small></th>
                                    @endforeach
                                    <th style="vertical-align: middle;">Total Hadir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr class="user-row">
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            <input type="hidden" name="users[]" value="{{ $user->id }}">
                                        </td>
                                        @foreach($period as $date)
                                            <td class="text-center">
                                                <input type="checkbox" name="kehadiran[{{ $user->id }}][]"
                                                    value="{{ $date->format('Y-m-d') }}" class="absensi-check"
                                                    data-user-id="{{ $user->id }}">
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span id="total-{{ $user->id }}" class="badge badge-primary">0</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success float-right">Simpan Semua Absensi</button>
                </div>
            </form>
        @else
            <div class="card-body text-center text-muted">
                <h5>Silakan pilih rentang tanggal untuk memunculkan daftar absensi.</h5>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(document).on('change', '.absensi-check', function () {
            let userId = $(this).data('user-id');
            let totalHadir = $('.absensi-check[data-user-id="' + userId + '"]:checked').length;
            $('#total-' + userId).text(totalHadir);
        });
    </script>
@endsection