<a href="{{ route('premiHadir.print', $absensi->id) }}" target="_blank" class="btn btn-primary">
    Print premi
</a>
<a href="{{ route('sewa.print', $absensi->id) }}" target="_blank" class="btn btn-primary">
    Print Sewa
</a>
<h5>
    Periode: {{ $absensi->tanggal_mulai }}
    s/d
    {{ $absensi->tanggal_akhir }}
</h5>   

<table class="table table-bordered table-sm mt-3">
    <thead class="thead-light">
        <tr>
            <th>Karyawan</th>
            <th class="text-center">Hadir</th>
            <th class="text-right">sub Premi</th>
            <th class="text-right">sub Sewa</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($absensi->premiHadirs as $p)
        <tr>
            <td>{{ $p->user->name }}</td>
            <td class="text-center">{{ $p->total_hadir }}</td>
            <td class="text-right">
                Rp {{ number_format($p->subtotal_premi,0,',','.') }}
            </td>
            <td class="text-right">
                Rp {{ number_format($p->subtotal_sewa,0,',','.') }}
            </td>
            <td class="text-right font-weight-bold">
                Rp {{ number_format($p->total_keseluruhan,0,',','.') }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>