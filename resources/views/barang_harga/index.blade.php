@extends('layouts.admin')

@section('content')

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h3>
                Harga Barang : {{ $barang->nama_barang }}
            </h3>

            <a href="{{ route('barang.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </div>


        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-3">
                    <input type="number" id="min_qty" class="form-control" placeholder="Min Qty">
                </div>

                <div class="col-md-3">
                    <input type="number" id="harga" class="form-control" placeholder="Harga">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary" id="btnTambah">
                        Tambah
                    </button>
                </div>

            </div>


            <table class="table table-bordered">

                <thead>

                    <tr>
                        <th>Min Qty</th>
                        <th>Harga</th>
                        <th width="100">Aksi</th>
                    </tr>

                </thead>

                <tbody id="tableHarga">

                    @foreach($hargas as $h)

                        <tr data-id="{{ $h->id }}">

                            <td>{{ $h->min_qty }}</td>

                            <td>{{ number_format($h->harga) }}</td>

                            <td>

                                <button class="btn btn-danger btn-sm btnHapus">

                                    Hapus

                                </button>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            $('#btnTambah').click(function () {

                let min_qty = $('#min_qty').val();
                let harga = $('#harga').val();

                $.ajax({

                    url: "{{ route('barang.harga.store', $barang->id) }}",

                    type: "POST",

                    data: {
                        min_qty: min_qty,
                        harga: harga,
                        _token: "{{ csrf_token() }}"
                    },

                    success: function () {

                        location.reload();

                    }

                });

            });


            $(document).on('click', '.btnHapus', function () {

                let row = $(this).closest('tr');

                let id = row.data('id');

                if (!confirm('hapus harga ini?')) return;

                $.ajax({

                    url: "{{ url('barang/' . $barang->id . '/harga') }}/" + id,

                    type: "DELETE",

                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: function () {

                        row.remove();

                    }

                });

            });


        });
    </script>
@endsection