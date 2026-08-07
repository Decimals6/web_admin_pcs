@extends('layouts.admin')

@section('content')

    <div class="card">
        <div class="card-header">
            <h4>Daftar Pembayaran</h4>
        </div>

        <div class="card-body">

            <form method="GET" class="row mb-3 align-items-end">

                <div class="col-md-3">
                    <label>Dari Tanggal</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label>Customer</label>

                    <select class="form-control" name="customer_id">
                        <option value="">Semua Customer</option>

                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->nama_customer }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="col-md-3">

                    <button class="btn btn-primary">
                        Filter
                    </button>

                    <a href="{{ route('penjualan.payment.index') }}" class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </form>

            <table class="table table-bordered table-striped">

                <thead class="bg-secondary text-white">

                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Dana Diterima</th>
                        <th>Dana Dibayarkan</th>
                        <th>Deduction</th>
                        <th>Aksi</th>
                    </tr>

                </thead>

                <tbody>

                    @forelse($payments as $index => $payment)

                        <tr>

                            <td>{{ $payments->firstItem() + $index }}</td>

                            <td>
                                {{ $payment->created_at->format('d-m-Y') }}
                            </td>

                            <td>
                                {{ $payment->customer->nama_customer ?? '-' }}
                            </td>

                            <td>
                                Rp {{ number_format($payment->received, 0, ',', '.') }}
                            </td>

                            <td>
                                Rp {{ number_format($payment->total, 0, ',', '.') }}
                            </td>

                            <td>
                                Rp {{ number_format($payment->deduction, 0, ',', '.') }}
                            </td>

                            <td>

                                <button class="btn btn-info btn-sm btn-detail"
                                    data-url="{{ route('penjualan.payment.show', $payment->id) }}">
                                    Detail
                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center">
                                Tidak ada data
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

            <div class="d-flex justify-content-between mt-3">

                <div>

                    Menampilkan

                    {{ $payments->firstItem() ?? 0 }}

                    -

                    {{ $payments->lastItem() ?? 0 }}

                    dari

                    {{ $payments->total() }}

                    data

                </div>

                <div>

                    {{ $payments->links() }}

                </div>

            </div>

        </div>
    </div>

    {{-- Modal --}}

    <div class="modal fade" id="detailModal">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header">

                    <h5>Detail Pembayaran</h5>

                    <button class="close" data-dismiss="modal">

                        &times;

                    </button>

                </div>

                <div class="modal-body">

                    <div id="detailContent">
                        Loading...
                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

@section('scripts')

    <script>

        $(document).on('click', '.btn-detail', function () {

            let url = $(this).data('url');

            $('#detailModal').modal('show');

            $('#detailContent').html(`
            <div class="text-center p-4">
                <div class="spinner-border"></div>
                <br>
                Loading...
            </div>
        `);

            $.ajax({

                url: url,
                type: 'GET',

                success: function (html) {

                    $('#detailContent').html(html);

                },

                error: function () {

                    $('#detailContent').html(`
                    <div class="alert alert-danger">
                        Gagal memuat data.
                    </div>
                `);

                }

            });

        });

    </script>

@endsection