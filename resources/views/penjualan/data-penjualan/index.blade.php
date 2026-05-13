@extends('layouts.admin')

@section('content')
<div class="card">

    {{-- HEADER --}}
    <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Data Penjualan</h4>

            <div>
                {{-- Export sesuai filter --}}
                <a href="{{ route('penjualan.data-penjualan.export', request()->query()) }}"
                   class="btn btn-success">
                    Export Semua (Filter)
                </a>

                {{-- Export halaman aktif --}}
                <a href="{{ route('penjualan.data-penjualan.export', array_merge(request()->query(), ['page' => request('page')])) }}"
                   class="btn btn-warning">
                    Export Halaman Ini
                </a>

                {{-- Print --}}
                <a href="{{ route('penjualan.data-penjualan.print', request()->query()) }}"
                   target="_blank"
                   class="btn btn-primary">
                    Print
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">

        {{-- ================= FILTER ================= --}}
        <form method="GET" class="mb-4">
            <div class="row align-items-end">

                <div class="col-md-3">
                    <label>Dari Tanggal</label>
                    <input type="date" name="from"
                           value="{{ request('from') }}"
                           class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="to"
                           value="{{ request('to') }}"
                           class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control">
                        <option value="">-- Semua Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->nama_customer }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('penjualan.data-penjualan.index') }}"
                       class="btn btn-secondary">
                       Reset
                    </a>
                </div>

            </div>
        </form>
        {{-- ================= END FILTER ================= --}}


        {{-- ================= SUMMARY KESELURUHAN ================= --}}
        <div class="alert alert-info">
            <strong>Total Sesuai Filter:</strong><br>
            DPP: {{ number_format($totalDpp,0,',','.') }} |
            PPN: {{ number_format($totalPpn,0,',','.') }} |
            Grand Total: {{ number_format($grandTotal,0,',','.') }}
        </div>


        {{-- ================= TABLE ================= --}}
        <table class="table table-bordered table-striped">
            <thead class="bg-secondary text-white">
                <tr>
                    <th>No</th>
                    <th>No. Inv</th>
                    <th>Tgl Invoice</th>
                    <th>Nama Customer</th>
                    <th>DPP</th>
                    <th>PPN</th>
                    <th>Total</th>
                    {{-- <th>Qty</th> --}}
                    <th>Metode Bayar</th>
                    <th>Tgl Bayar</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $pageDpp = 0;
                    $pagePpn = 0;
                    $pageGrand = 0;
                    $pageQty = 0;
                @endphp

                @forelse($invoices as $index => $inv)

                    @php
                        $qty = $inv->details->sum(fn($d) => $d->orderDetail->qty ?? 0);

                        $pageDpp   += $inv->dpp;
                        $pagePpn   += $inv->ppn;
                        $pageGrand += $inv->grand_total;
                        $pageQty   += $qty;

                        $paid = $inv->paymentDetails->sum('subtotal');
    $sisa = $inv->grand_total - $paid;

    if ($paid == 0) {
        $rowClass = 'text-danger fw-bold';
    } elseif ($sisa > 0) {
        $rowClass = 'text-warning fw-bold';
    } else {
        $rowClass = 'text-success fw-bold';
    }

    $lastPaymentDetail = $inv->paymentDetails
        ->sortByDesc(fn($pd) => $pd->payment->created_at ?? null)
        ->first();

    $ket = $lastPaymentDetail?->payment?->keterangan ?? '';

    if (str_contains($ket, 'TF')) {
        $metode = 'Transfer';
    } elseif (str_contains($ket, 'Cash')) {
        $metode = 'Cash';
    } else {
        $metode = '-';
    }

    $tglBayar = $lastPaymentDetail?->payment?->created_at?->format('d-m-Y') ?? '-';

                    @endphp

                    <tr class="{{ $rowClass }} invoice-row"
    data-id="{{ $inv->id }}"
    data-no="{{ $inv->no }}"
    data-customer="{{ $inv->customer->nama_customer ?? '-' }}">
                        <td>{{ $invoices->firstItem() + $index }}</td>
                        <td>{{ $inv->no }}</td>
                        <td>{{ $inv->tgl->format('d-m-Y') }}</td>
                        <td>{{ $inv->customer->nama_customer ?? '-' }}</td>
                        <td>{{ number_format($inv->dpp,0,',','.') }}</td>
                        <td>{{ number_format($inv->ppn,0,',','.') }}</td>
                        <td>{{ number_format($inv->grand_total,0,',','.') }}</td>
                        {{-- <td>{{ $qty }}</td> --}}
                        <td>{{ $metode ?? '-' }}</td>
                        <td>{{ $tglBayar ?? '-' }}</td>
                        <td>{{ $inv->keterangan ?? '-' }}</td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>

            @if($invoices->count())
            <tfoot class="bg-light fw-bold">
                <tr>
                    <td colspan="4" class="text-end">TOTAL HALAMAN INI</td>
                    <td>{{ number_format($pageDpp,0,',','.') }}</td>
                    <td>{{ number_format($pagePpn,0,',','.') }}</td>
                    <td>{{ number_format($pageGrand,0,',','.') }}</td>
                    {{-- <td>{{ $pageQty }}</td> --}}
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
 <!-- MODAL DETAIL PEMBAYARAN -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Detail Pembayaran</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <p><strong>No Inv :</strong> <span id="modal-no"></span></p>
        <p><strong>Customer :</strong> <span id="modal-customer"></span></p>

        <hr>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Tgl Bayar</th>
              <th>Nominal Bayar</th>
              <th>Metode Bayar</th>
            </tr>
          </thead>
          <tbody id="modal-payment-body">
              <tr>
                <td colspan="3" class="text-center text-muted">
                  Tidak ada pembayaran
                </td>
              </tr>
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>

        {{-- ================= INFO + PAGINATION ================= --}}
        <div class="d-flex justify-content-between align-items-center mt-3">

            <div>
                Menampilkan
                {{ $invoices->firstItem() ?? 0 }}
                –
                {{ $invoices->lastItem() ?? 0 }}
                dari
                {{ $invoices->total() }} data
            </div>

            <div>
                {{ $invoices->links() }}
            </div>

        </div>

    </div>
</div>

<script>
document.querySelectorAll('.invoice-row').forEach(row => {

    row.addEventListener('click', function(){

        // highlight selected
        document.querySelectorAll('.invoice-row')
            .forEach(r => r.classList.remove('table-primary'));

        this.classList.add('table-primary');

        let invoiceId = this.dataset.id;
        let no = this.dataset.no;
        let customer = this.dataset.customer;

        document.getElementById('modal-no').innerText = no;
        document.getElementById('modal-customer').innerText = customer;

        fetch(`/api/piutang/${invoiceId}/payments`)
            .then(res => res.json())
            .then(data => {

                let tbody = document.getElementById('modal-payment-body');
                tbody.innerHTML = '';

                if(data.length === 0){
                    tbody.innerHTML =
                      `<tr>
                        <td colspan="3" class="text-center text-muted">
                          Tidak ada pembayaran
                        </td>
                      </tr>`;
                    return;
                }

                data.forEach(item => {

                    tbody.innerHTML += `
                        <tr>
                            <td>${item.tgl}</td>
                            <td>${item.nominal}</td>
                            <td>${item.metode}</td>
                        </tr>
                    `;
                });

            });

        let modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();

    });

});
</script>
@endsection
