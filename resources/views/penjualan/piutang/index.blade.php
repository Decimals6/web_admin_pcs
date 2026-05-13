@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Laporan Piutang</h4>
        </div>

        <div class="card-body">

            <!-- =================== -->
            <!--  ATAS - CUSTOMER   -->
            <!-- =================== -->

            <div class="scroll-box mb-3">
                <table class="table table-bordered table-striped mb-0" id="customer-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>No</th>
                            <th>Nama Customer</th>
                            <th>Total Piutang</th>
                            <th>Terbayar</th>
                            <th>Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $i => $customer)

                            @php
                                $total = 0;
                                $paid = 0;

                                foreach ($customer->invoices as $inv) {
                                    $total += $inv->grand_total + $inv->ongkirs->sum('nominal');
                                    $paid += $inv->paymentDetails->sum('subtotal');
                                }

                                $sisa = $total - $paid;
                            @endphp

                            @if($sisa > 0)
                                <tr class="customer-row" data-id="{{ $customer->id }}" data-sisa="{{ $sisa }}">
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $customer->nama_customer }}</td>
                                    <td>{{ number_format($total, 0, ',', '.') }}</td>
                                    <td>{{ number_format($paid, 0, ',', '.') }}</td>
                                    <td class="text-danger fw-bold">
                                        {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif

                        @endforeach
                    </tbody>
                </table>
            </div>

            <hr>

            <!-- =================== -->
            <!--  DETAIL INVOICE    -->
            <!-- =================== -->

            <h5>Detail Invoice Customer</h5>

            <div class="scroll-box mb-3">
                <table class="table table-bordered mb-0" id="invoice-detail-table">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Tgl Invoice</th>
                            <th>No Invoice</th>
                            <th>No SO</th>
                            <th>Jatuh Tempo</th>
                            <th>Total</th>
                            <th>Terbayar</th>
                            <th>Kurang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Pilih customer di atas
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <h5>Pelunasan Piutang</h5>

            <form method="POST" action="{{ route('penjualan.piutang.bayar') }}">
                @csrf

                <input type="hidden" name="customer_id" id="customer_id_input">

                <div class="row">
                    <div class="col-md-3">
                        <label>Tanggal Pembayaran</label>
                        <input type="date" name="tgl" value="{{ date('Y-m-d') }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Total Bayar</label>
                        <input type="number" name="jumlah_bayar" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label>Metode</label>
                        <select name="metode" class="form-control">
                            <option value="TF">Transfer</option>
                            <option value="Cash">Cash</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-success form-control">
                            Bayar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection


@section('scripts')

    <style>
        .scroll-box {
            max-height: 350px;
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #dee2e6;
        }

        /* Sticky header optional */
        .scroll-box thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #customer-table thead th {
            background: #6c757d;
            color: white;
        }

        #invoice-detail-table thead th {
            background: #f8f9fa;
            color: black;
        }

        .customer-row.active {
            background-color: #d1ecf1 !important;
            cursor: pointer;
        }

        .invoice-row.active {
            background-color: #ffeeba !important;
            cursor: pointer;
        }

        .customer-row,
        .invoice-row {
            cursor: pointer;
        }
    </style>

    <script>
        function activateInvoiceClick() {
            document.querySelectorAll('.invoice-row').forEach(row => {
                row.addEventListener('click', function () {
                    document.querySelectorAll('.invoice-row')
                        .forEach(r => r.classList.remove('active'));

                    this.classList.add('active');

                    let sisa = parseInt(this.dataset.sisa);
                    document.querySelector('input[name="jumlah_bayar"]').value = sisa;
                });
            });
        }

        document.querySelectorAll('.customer-row').forEach(row => {
            row.addEventListener('click', function () {

                document.querySelectorAll('.customer-row')
                    .forEach(r => r.classList.remove('active'));

                this.classList.add('active');

                let customerId = this.dataset.id;
                let totalSisa = this.dataset.sisa;

                document.getElementById('customer_id_input').value = customerId;
                document.querySelector('input[name="jumlah_bayar"]').value = totalSisa;

                fetch(`/api/piutang/${customerId}`)
                    .then(res => res.json())
                    .then(data => {

                        let tbody = document.querySelector('#invoice-detail-table tbody');
                        tbody.innerHTML = '';

                        if (data.length === 0) {
                            tbody.innerHTML =
                                '<tr><td colspan="8" class="text-center">Tidak ada piutang</td></tr>';
                            return;
                        }

                        data.forEach((item, index) => {
                            tbody.innerHTML += `
                            <tr class="invoice-row"
                                data-sisa="${item.sisa.replace(/\./g, '')}">
                                <td>${index + 1}</td>
                                <td>${item.tgl}</td>
                                <td>${item.no}</td>
                                <td>${item.no_so ?? '-'}</td>
                                <td>${item.jatuh_tempo}</td>
                                <td>${item.total}</td>
                                <td>${item.paid}</td>
                                <td style="color:red;font-weight:bold;">
                                    ${item.sisa}
                                </td>
                            </tr>
                        `;
                        });

                        activateInvoiceClick();
                    });
            });
        });
    </script>

@endsection