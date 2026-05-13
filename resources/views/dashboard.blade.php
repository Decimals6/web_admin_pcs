@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-6 py-6">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Dashboard Keuangan
        </h1>
        <p class="text-gray-500">
            Ringkasan performa bisnis tahun {{ date('Y') }}
        </p>
    </div>



   {{-- Summary Cards --}}
{{-- Summary Cards --}}
<div class="row g-3">

    {{-- Penjualan --}}
    <div class="col-md">
        <div class="card border-0 text-white shadow-sm h-100"
             style="background: linear-gradient(135deg,#16a34a,#22c55e); border-radius:14px;">
            <div class="card-body py-3 px-3">
                <p class="mb-1" style="font-size:13px; opacity:.8;">
                    Total Penjualan
                </p>
                <h4 class="fw-bold mb-0" style="font-size:20px;">
                    Rp {{ number_format($totalPenjualan,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    {{-- Pembelian --}}
    <div class="col-md">
        <div class="card border-0 text-white shadow-sm h-100"
             style="background: linear-gradient(135deg,#dc2626,#ef4444); border-radius:14px;">
            <div class="card-body py-3 px-3">
                <p class="mb-1" style="font-size:13px; opacity:.8;">
                    Total Pembelian
                </p>
                <h4 class="fw-bold mb-0" style="font-size:20px;">
                    Rp {{ number_format($totalPembelian,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    {{-- Saldo Kas --}}
    <div class="col-md">
        <div class="card border-0 text-white shadow-sm h-100"
             style="background: linear-gradient(135deg,#2563eb,#3b82f6); border-radius:14px;">
            <div class="card-body py-3 px-3">
                <p class="mb-1" style="font-size:13px; opacity:.8;">
                    Saldo Kas
                </p>
                <h4 class="fw-bold mb-0" style="font-size:20px;">
                    Rp {{ number_format($saldoKas,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    {{-- Hutang --}}
    <div class="col-md">
        <div class="card border-0 text-white shadow-sm h-100"
             style="background: linear-gradient(135deg,#7c3aed,#a855f7); border-radius:14px;">
            <div class="card-body py-3 px-3">
                <p class="mb-1" style="font-size:13px; opacity:.8;">
                    Total Hutang
                </p>
                <h4 class="fw-bold mb-0" style="font-size:20px;">
                    Rp {{ number_format($totalHutang,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

    {{-- Piutang --}}
    <div class="col-md">
        <div class="card border-0 text-white shadow-sm h-100"
             style="background: linear-gradient(135deg,#f59e0b,#fbbf24); border-radius:14px;">
            <div class="card-body py-3 px-3">
                <p class="mb-1" style="font-size:13px; opacity:.8;">
                    Total Piutang
                </p>
                <h4 class="fw-bold mb-0" style="font-size:20px;">
                    Rp {{ number_format($totalPiutang,0,',','.') }}
                </h4>
            </div>
        </div>
    </div>

</div>

    {{-- Chart --}}
    <div class="bg-white rounded-2xl shadow-md p-6 mt-5"> 
        <h3 class="text-lg font-semibold text-gray-700 mb-4"> 
            Grafik Penjualan vs Pembelian ({{ date('Y') }}) 
        </h3> 
        <canvas id="financeChart" height="65"></canvas> 
    </div>

</div>

{{-- ChartJS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');

    const penjualanData = @json($penjualanBulanan);
    const pembelianData = @json($pembelianBulanan);

    const months = Array.from({length: 12}, (_, i) => i + 1);

    const penjualan = months.map(m => penjualanData[m] ?? 0);
    const pembelian = months.map(m => pembelianData[m] ?? 0);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
            datasets: [
                {
                    label: 'Penjualan',
                    data: penjualan,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.2)',
                    tension: 0.3
                },
                {
                    label: 'Pembelian',
                    data: pembelian,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220,38,38,0.2)',
                    tension: 0.3
                }
            ]
        }
    });
</script>

@endsection