<div class="row">

    <div class="col-md-6">

        <table class="table table-bordered">

            <tr>
                <th>Customer</th>
                <td>{{ $payment->customer->nama_customer ?? '-' }}</td>
            </tr>

            <tr>
                <th>Total Pembayaran</th>
                <td>
                    Rp {{ number_format($payment->total, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <th>Dana Diterima</th>
                <td>
                    Rp {{ number_format($payment->received, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <th>Deduction</th>
                <td>
                    Rp {{ number_format($payment->deduction, 0, ',', '.') }}
                </td>
            </tr>

            <tr>
                <th>Catatan</th>
                <td>{{ $payment->deduction_note ?: '-' }}</td>
            </tr>

            <tr>
                <th>Keterangan</th>
                <td>{{ $payment->keterangan ?: '-' }}</td>
            </tr>

        </table>

    </div>

</div>

<hr>

<h5>Invoice Yang Dibayar</h5>

<table class="table table-bordered table-striped">

    <thead class="bg-secondary text-white">

        <tr>

            <th>No</th>
            <th>No Invoice</th>
            <th>Tanggal</th>
            <th>Grand Total</th>
            <th>Subtotal Dibayar</th>

        </tr>

    </thead>

    <tbody>

        @foreach($payment->details as $i => $detail)

            <tr>

                <td>{{ $i + 1 }}</td>

                <td>{{ $detail->invoice->no }}</td>

                <td>{{ $detail->invoice->tgl->format('d-m-Y') }}</td>

                <td>
                    Rp {{ number_format($detail->invoice->grand_total, 0, ',', '.') }}
                </td>

                <td>
                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>