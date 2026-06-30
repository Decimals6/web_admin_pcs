<h5 class="modal-title">
    Detail Penawaran - {{ $penawaran->no_penawaran }}
</h5>

<div class="mb-3">
    <a href="{{ route('penjualan.penawaran.print', $penawaran->id) }}" target="_blank" class="btn btn-primary btn-sm">
        <i class="fas fa-file-pdf"></i> Print PDF
    </a>
</div>

<div class="mb-3">
    <table class="table table-sm table-borderless">
        <tr>
            <th width="150">No. Penawaran</th>
            <td>{{ $penawaran->no_penawaran }}</td>
        </tr>
        <tr>
            <th>Perihal</th>
            <td>{{ $penawaran->perihal ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal</th>
            <td>{{ \Carbon\Carbon::parse($penawaran->tanggal)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Berlaku Hingga</th>
            <td>
                {{ $penawaran->berlaku_hingga ? \Carbon\Carbon::parse($penawaran->berlaku_hingga)->format('d-m-Y') : '-' }}
                @if($penawaran->isExpired())
                    <span class="badge bg-danger">Kadaluarsa</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Customer</th>
            <td>{{ $penawaran->customer->nama_customer ?? '-' }}</td>
        </tr>
        <tr>
            <th>Up (PIC)</th>
            <td>{{ $penawaran->up ?? '-' }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @php
                    $badgeClass = match($penawaran->status) {
                        'draft' => 'bg-secondary',
                        'terkirim' => 'bg-info',
                        'diterima' => 'bg-success',
                        'ditolak' => 'bg-danger',
                        default => 'bg-secondary',
                    };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ ucfirst($penawaran->status) }}</span>
            </td>
        </tr>
        <tr>
            <th>Catatan</th>
            <td>{{ $penawaran->catatan ?? '-' }}</td>
        </tr>
    </table>
</div>

<hr>

<h6>Detail Barang</h6>

@foreach($penawaran->barangPenawaran as $item)
    <div class="card mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong>{{ $loop->iteration }}. {{ $item->nama_snapshot }}</strong>
            <span class="badge {{ $item->tipe === 'consumable' ? 'bg-primary' : 'bg-warning text-dark' }}">
                {{ $item->tipe === 'consumable' ? 'Habis Pakai' : 'Equipment' }}
            </span>
        </div>
        <div class="card-body">

            @if($item->keterangan)
                <p class="text-muted small mb-2">{{ $item->keterangan }}</p>
            @endif

            {{-- SPESIFIKASI --}}
            @if($item->spekPenawaran->count() > 0)
                <table class="table table-sm table-bordered mb-3">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th style="width:30%">Spesifikasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->spekPenawaran as $spek)
                            <tr>
                                <td>{{ $spek->nama_spek }}</td>
                                <td>{{ $spek->keterangan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- HARGA / TIER --}}
            <table class="table table-sm table-bordered mb-0">
                <thead class="bg-secondary text-white">
                    <tr>
                        @if($item->tipe === 'consumable')
                            <th>Min. Qty</th>
                            <th>Harga / {{ $item->satuan }}</th>
                        @else
                            <th colspan="2">Harga Satuan ({{ $item->satuan }})</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->hargaPenawaran as $harga)
                        <tr>
                            @if($item->tipe === 'consumable')
                                <td>{{ number_format($harga->min_qty, 0, ',', '.') }}</td>
                                <td>{{ number_format($harga->harga, 0, ',', '.') }}</td>
                            @else
                                <td colspan="2">{{ number_format($harga->harga, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
@endforeach