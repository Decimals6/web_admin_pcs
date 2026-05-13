@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header bg-dark text-white">
        <h4>Buat Purchase Order</h4>
    </div>
 
    <form action="{{ route('pembelian.purchase-order.store') }}" method="POST" id="poForm">
        @csrf
        <div class="card-body">

            <div class="row mb-2">
                {{-- <div class="col-md-3">
                    <label>No. PO</label>
                    <input type="text" name="no" class="form-control" value="{{ generateDocumentNumber('orders','PCS-PO') }}" readonly style="background-color: #e9ecef;">
                </div> --}}
    <div class="col-md-3">
        <label>No. PO</label>
        <input type="text" name="no" class="form-control">
    </div>


                <div class="col-md-2">
                    <label>Tanggal PO</label>
                    <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="col-md-3">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>TOP (Hari)</label>
                    <input type="number" name="top" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <label>Tanggal Kirim</label>
                    <input type="date" name="tgl_kirim" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <hr>
            <h5>Detail Barang</h5>
            <table class="table table-bordered" id="items-table">
                <thead class="bg-secondary text-white">
                    <tr>
    <th style="width:50%">Barang</th>
    <th style="width:10%">Qty</th>
    <th style="width:20%">Harga</th>
    <th style="width:15%">Subtotal</th>
    <th style="width:5%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="barang_id[]" class="form-control barang-select" required>
                                <option value="">-- Pilih Barang --</option>
                                @foreach(\App\Models\Barang::all() as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="qty[]" class="form-control qty" min="0" step="0.01" required></td>
                        <td><input type="number" name="harga[]" class="form-control harga" min="0" step="0.01" required></td>
                        <td><input type="number" name="subtotal_detail[]" class="form-control subtotal-detail" readonly style="background-color:#e9ecef;"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-primary btn-sm" id="add-row">+ Tambah Barang</button>

            <hr>
            <div class="row mb-3">
    <div class="col-md-3">
        <label>Gunakan PPN</label>
        <select name="use_ppn" id="use_ppn" class="form-control">
            <option value="1" selected>Pakai PPN</option>
            <option value="0">Tidak Pakai PPN</option>
        </select>
    </div>
</div>
            <div class="row">
                <div class="col-md-3">
                    <label>Subtotal (DPP)</label>
                    <input type="number" name="dpp" id="dpp" class="form-control" readonly style="background-color:#e9ecef;">
                </div>
                <div class="col-md-3">
                    <label>Pajak 11%</label>
                    <input type="number" name="pajak" id="pajak" class="form-control" readonly style="background-color:#e9ecef;">
                </div>
                <div class="col-md-3">
                    <label>Total</label>
                    <input type="number" name="total" id="total" class="form-control" readonly style="background-color:#e9ecef;">
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <input type="text" name="status" class="form-control" value="Belum Lunas" readonly style="background-color:#e9ecef;">
                </div>
            </div>

        </div>

        <div class="card-footer">
            <button class="btn btn-success">Simpan PO</button>
            <a href="{{ route('pembelian.purchase-order.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection

{{-- @section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){

    function calculateRow(row){
        let qtyInput = row.querySelector('.qty');
        let hargaInput = row.querySelector('.harga');
        let subtotalInput = row.querySelector('.subtotal-detail');

        let qty = parseFloat(qtyInput.value);
        let harga = parseFloat(hargaInput.value);

        if (isNaN(qty)) qty = 0;
        if (isNaN(harga)) harga = 0;

        let subtotal = qty * harga;
        subtotalInput.value = subtotal.toFixed(2);

        calculateTotal();
    }
    document.getElementById('add-row').addEventListener('click', function(){

    let tbody = document.querySelector('#items-table tbody');
    let firstRow = tbody.querySelector('tr');
    let newRow = firstRow.cloneNode(true);

    // Reset semua input
    newRow.querySelectorAll('input').forEach(function(input){
        input.value = '';
    });

    // Reset select
    newRow.querySelectorAll('select').forEach(function(select){
        select.selectedIndex = 0;
    });

    tbody.appendChild(newRow);

});

document.getElementById('items-table').addEventListener('click', function(e){
    if(e.target.classList.contains('remove-row')){
        let tbody = document.querySelector('#items-table tbody');
        if(tbody.rows.length > 1){
            e.target.closest('tr').remove();
            calculateTotal();
        }
    }
});

    function calculateTotal(){
        let dpp = 0;

        document.querySelectorAll('.subtotal-detail').forEach(function(input){
            let value = parseFloat(input.value);
            if (!isNaN(value)) {
                dpp += value;
            }
        });

        let pajak = dpp * 0.11;
        let total = dpp + pajak;

        document.getElementById('dpp').value = dpp.toFixed(2);
        document.getElementById('pajak').value = pajak.toFixed(2);
        document.getElementById('total').value = total.toFixed(2);
    }

    // Event delegation (lebih aman untuk row dinamis)
    document.getElementById('items-table').addEventListener('input', function(e){
        if(e.target.classList.contains('qty') || e.target.classList.contains('harga')){
            let row = e.target.closest('tr');
            calculateRow(row);
        }
    });

});

</script>
@endsection --}}
<style>

.select2-container--default .select2-selection--single {
    height: 38px !important;
    display: flex !important;
    align-items: center !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: normal !important;
    padding-left: 8px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}

</style>
@section('scripts')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function(){

    /* ===============================
       INIT SELECT2
    =============================== */

    function initSelect2(element){
        element.select2({
            placeholder: "Cari barang",
            width: '100%'
        });
    }

    initSelect2($('.barang-select'));


    /* ===============================
       HITUNG SUBTOTAL PER ROW
    =============================== */

    function calculateRow(row){

        let qty = parseFloat($(row).find('.qty').val());
        let harga = parseFloat($(row).find('.harga').val());

        if(isNaN(qty)) qty = 0;
        if(isNaN(harga)) harga = 0;

        let subtotal = qty * harga;

        $(row).find('.subtotal-detail').val(subtotal.toFixed(2));

        calculateTotal();
    }


    /* ===============================
       HITUNG TOTAL
    =============================== */

    function calculateTotal(){

        let dpp = 0;

        $('.subtotal-detail').each(function(){

            let val = parseFloat($(this).val());

            if(!isNaN(val)){
                dpp += val;
            }

        });

        let usePpn = $('#use_ppn').val();

        let pajak = 0;

        if(usePpn == 1){
            pajak = dpp * 0.11;
        }

        let total = dpp + pajak;

        $('#dpp').val(dpp.toFixed(2));
        $('#pajak').val(pajak.toFixed(2));
        $('#total').val(total.toFixed(2));
    }


    /* ===============================
       TAMBAH ROW BARANG
    =============================== */

    $('#add-row').click(function(){

        let tbody = $('#items-table tbody');
        let firstRow = tbody.find('tr:first');

        // destroy select2 sebelum clone
        firstRow.find('.barang-select').select2('destroy');

        // clone row
        let newRow = firstRow.clone();

        // reset input
        newRow.find('input').val('');

        // reset select
        newRow.find('select').prop('selectedIndex',0);

        tbody.append(newRow);

        // init select2 lagi
        initSelect2($('.barang-select'));

    });


    /* ===============================
       HAPUS ROW
    =============================== */

    $('#items-table').on('click','.remove-row',function(){

        let tbody = $('#items-table tbody');

        if(tbody.find('tr').length > 1){

            $(this).closest('tr').remove();

            calculateTotal();

        }

    });


    /* ===============================
       AUTO HITUNG SAAT INPUT
    =============================== */

    $('#items-table').on('input','.qty, .harga',function(){

        let row = $(this).closest('tr');

        calculateRow(row);

    });


    /* ===============================
       TOGGLE PPN
    =============================== */

    $('#use_ppn').on('change',function(){

        calculateTotal();

    });

});
</script>

@endsection

