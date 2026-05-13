@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-dark text-white">
        <h4>Laporan Hutang</h4>
    </div>

    <div class="card-body">

        <!-- =================== -->
        <!--  ATAS - SUPPLIER   -->
        <!-- =================== -->

        <div class="scroll-box mb-3">
            <table class="table table-bordered table-striped mb-0" id="supplier-table">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th>No</th>
                        <th>Nama Supplier</th>
                        <th>Total Hutang</th>
                        <th>Terbayar</th>
                        <th>Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $i => $supplier)

                        @php
                            $total = 0;
                            $paid = 0;

                            foreach($supplier->invoices as $inv){
                                $total += $inv->grand_total;
                                $paid += $inv->paymentDetails->sum('subtotal');
                            }

                            $sisa = $total - $paid;
                        @endphp

                        @if($sisa > 0)
                        <tr class="supplier-row"
                            data-id="{{ $supplier->id }}"
                            data-sisa="{{ $sisa }}">
                            <td>{{ $i+1 }}</td>
                            <td>{{ $supplier->nama_supplier }}</td>
                            <td>{{ number_format($total,0,',','.') }}</td>
                            <td>{{ number_format($paid,0,',','.') }}</td>
                            <td class="text-danger fw-bold">
                                {{ number_format($sisa,0,',','.') }}
                            </td>
                        </tr>
                        @endif

                    @endforeach
                </tbody>
            </table>
        </div>

        <hr>

        <!-- =================== -->
        <!--  BAWAH - DETAIL    -->
        <!-- =================== -->

        <h5>Detail Invoice Supplier</h5>

        <div class="scroll-box mb-3">
            <table class="table table-bordered mb-0" id="invoice-detail-table">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Tgl Invoice</th>
                        <th>No Invoice</th>
                        <th>No PO</th>
                        <th>Jatuh Tempo</th>
                        <th>Total</th>
                        <th>Terbayar</th>
                        <th>Kurang</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Pilih supplier di atas
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

        <h5>Pelunasan Hutang</h5>

        <form method="POST" action="{{ route('pembelian.hutang.bayar') }}">
            @csrf

            <input type="hidden" name="supplier_id" id="supplier_id_input">

            <div class="row">
                <div class="col-md-3">
                    <label>Tanggal Pelunasan</label>
                    <input type="date"
                           name="tgl"
                           value="{{ date('Y-m-d') }}"
                           class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Total Bayar</label>
                    <input type="number"
                           name="jumlah_bayar"
                           class="form-control"
                           required>
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
    max-height: 350px; /* tinggi fix */
    overflow-y: auto;
    overflow-x: hidden;
    border: 1px solid #dee2e6;
}

/* optional sticky header */
.scroll-box thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}

#supplier-table thead th {
    background: #6c757d;
    color: white;
}

#invoice-detail-table thead th {
    background: #f8f9fa;
    color: black;
}

.supplier-row.active {
    background-color: #d1ecf1 !important;
    cursor: pointer;
}

.invoice-row.active {
    background-color: #ffeeba !important;
    cursor: pointer;
}

.supplier-row,
.invoice-row {
    cursor: pointer;
}
</style>

<script>

function activateInvoiceClick() {
    document.querySelectorAll('.invoice-row').forEach(row => {
        row.addEventListener('click', function(){
            document.querySelectorAll('.invoice-row')
                .forEach(r => r.classList.remove('active'));

            this.classList.add('active');

            let sisa = parseInt(this.dataset.sisa);
            document.querySelector('input[name="jumlah_bayar"]').value = sisa;
        });
    });
}

document.querySelectorAll('.supplier-row').forEach(row => {

    row.addEventListener('click', function(){

        document.querySelectorAll('.supplier-row')
            .forEach(r => r.classList.remove('active'));

        this.classList.add('active');

        let supplierId = this.dataset.id;
        let totalSisa = this.dataset.sisa;

        document.getElementById('supplier_id_input').value = supplierId;
        document.querySelector('input[name="jumlah_bayar"]').value = totalSisa;

        fetch(`/api/hutang/${supplierId}`)
            .then(res => res.json())
            .then(data => {

                let tbody = document.querySelector('#invoice-detail-table tbody');
                tbody.innerHTML = '';

                if(data.length === 0){
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="text-center">Tidak ada hutang</td></tr>';
                    return;
                }

                data.forEach((item, index) => {

                    tbody.innerHTML += `
                        <tr class="invoice-row"
                            data-sisa="${item.sisa.replace(/\./g,'')}">
                            <td>${index+1}</td>
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