{{-- <div class="row mb-3">
    <div class="col-6">
        <strong>Periode:</strong> {{ $absensi->tanggal_mulai }} s/d {{ $absensi->tanggal_akhir }}
    </div>
    <div class="col-6 text-right">
        <strong>Keterangan:</strong> {{ $absensi->keterangan ?? '-' }}
    </div>
</div>

<table class="table table-sm table-bordered">
    <thead class="bg-light">
        <tr>
            <th>Karyawan</th>
            <th class="text-center">Hadir</th>
            <th class="text-right">Subtotal Premi</th>
            <th class="text-right">Subtotal Sewa</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($details as $d)
        <tr>
            <td>{{ $d->user->name }}</td>
            <td class="text-center">{{ $d->total_hadir }} hari</td>
            <td class="text-right">Rp {{ number_format($d->subtotal_premi) }}</td>
            <td class="text-right">Rp {{ number_format($d->subtotal_sewa) }}</td>
            <td class="text-right font-weight-bold">Rp {{ number_format($d->total_keseluruhan) }}</td>
        </tr>
        @endforeach
    </tbody>
</table> --}}

<div class="row mb-3">
    <div class="col-6">
        <strong>Periode:</strong>
        {{ $absensi->tanggal_mulai }} s/d {{ $absensi->tanggal_akhir }}
    </div>
    <div class="col-6 text-right">
        <strong>Keterangan:</strong> {{ $absensi->keterangan ?? '-' }}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm text-nowrap">
        <thead class="bg-light">
            <tr>
                <th>Nama Karyawan</th>
                @foreach($period as $date)
                    <th class="text-center">
                        {{ \Carbon\Carbon::parse($date)->format('d/m') }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>

                    @foreach($period as $date)
                        @php
                            $hadir = $absenDetails->where('user_id', $user->id)
                                ->where('tanggal', $date->format('Y-m-d'))
                                ->count();
                        @endphp

                        <td class="text-center">
                            @if($hadir)
                                <span class="text-success font-weight-bold">✔</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>