@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Surat Jalan Penjualan</h4>
        </div>

        <form action="{{ route('penjualan.delivery-note.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>No. Surat Jalan</label>
                        <div class="input-group">
                            <input type="text" name="no" id="noInvoice" class="form-control"
                                value="{{ generateDocumentNumber('delivery_notes', 'PCS-SJ', 'keluar') }}"
                                style="background-color: #e9ecef;" readonly required>
                            <button type="button" class="btn btn-outline-secondary" id="btnEditNo" title="Edit nomor manual">
                                <i class="fa fa-pencil-alt" id="iconEditNo"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label>Sales Order</label>
                        <select name="order_id" id="orderSelect" class="form-control select2-search" data-placeholder="-- Cari No. SO / Customer --">
                            <option value="" data-alamat="">-- Cari No. SO / Customer --</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->id }}" data-alamat="{{ $order->customer->alamat ?? '' }}">
                                    {{ $order->no }} | {{ \Carbon\Carbon::parse($order->tgl)->format('d-m-Y') }} | {{ $order->customer->nama_customer ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Alamat Kirim</label>
                        <input type="text" name="alamat_kirim" id="alamatKirim" class="form-control" required>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="mb-0">Detail Barang</h4>
                    <button type="button" class="btn btn-sm btn-success" id="btnTambahBaris" onclick="addAvailableRow()">+ Tambah Barang</button>
                </div>

                <table class="table table-bordered" id="detailTable">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th style="width:15%">Qty</th>
                            <th>Keterangan</th>
                            <th style="width:60px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('penjualan.delivery-note.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        border: 1px solid #ced4da !important;
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
    <script>
        let rowIndex = 0;
        let currentOrderDetails = [];

        function addRow(detail = null) {
            if (!detail) return;

            const tbody = document.querySelector('#detailTable tbody');
            let row = document.createElement('tr');
            let namaBarang = detail.barang ? detail.barang.nama_barang : (detail.nama || 'Tanpa Nama');

            row.innerHTML = `
                <td>
                    <input type="hidden" class="order-detail-id-input" name="details[${rowIndex}][order_detail_id]" value="${detail.id}">
                    <input type="text" class="form-control" value="${namaBarang}" readonly>
                </td>
                <td>
                    <input type="number"
                        name="details[${rowIndex}][qty]"
                        class="form-control qty-input"
                        min="1"
                        max="${detail.sisa}"
                        value="${detail.sisa}">
                </td>
                <td>
                    <input type="text" name="details[${rowIndex}][keterangan]" class="form-control">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">-</button>
                </td>
            `;

            tbody.appendChild(row);
            rowIndex++;
        }

        function addAvailableRow() {
            if (currentOrderDetails.length === 0) {
                alert('Silahkan pilih Sales Order terlebih dahulu.');
                return;
            }

            const existingIds = Array.from(document.querySelectorAll('.order-detail-id-input'))
                .map(input => parseInt(input.value));

            const itemToRestore = currentOrderDetails.find(detail => {
                let sisa = detail.qty - (detail.qty_sent || 0);
                return sisa > 0 && !existingIds.includes(detail.id);
            });

            if (itemToRestore) {
                let sisa = itemToRestore.qty - (itemToRestore.qty_sent || 0);
                itemToRestore.sisa = sisa;
                addRow(itemToRestore);
            } else {
                alert('Semua barang dari Sales Order ini sudah dimasukkan ke dalam tabel.');
            }
        }

        function removeRow(btn) {
            const tbody = document.querySelector('#detailTable tbody');
            btn.closest('tr').remove();

            if (tbody.rows.length === 0) {
                rowIndex = 0;
            }
        }

        $(document).ready(function () {
            $('#orderSelect').select2({
                placeholder: "-- Cari No. SO / Customer --",
                allowClear: true,
                width: '100%'
            });

            $('#orderSelect').on('change', function () {
                const orderId = $(this).val();
                const alamat = $(this).find('option:selected').data('alamat') || '';
                $('#alamatKirim').val(alamat);

                const tbody = document.querySelector('#detailTable tbody');

                tbody.innerHTML = '';
                rowIndex = 0;
                currentOrderDetails = [];

                if (!orderId) return;

                fetch(`/order/${orderId}/details`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data || data.length === 0) return;

                        currentOrderDetails = data;

                        data.forEach(detail => {
                            let sisa = detail.qty - (detail.qty_sent || 0);
                            if (sisa > 0) {
                                detail.sisa = sisa;
                                addRow(detail);
                            }
                        });
                    })
                    .catch(err => console.log(err));
            });

            const noInvoiceInput = document.getElementById('noInvoice');
            const btnEditNo = document.getElementById('btnEditNo');
            const iconEditNo = document.getElementById('iconEditNo');

            btnEditNo.addEventListener('click', function () {
                const isLocked = noInvoiceInput.hasAttribute('readonly');

                if (isLocked) {
                    noInvoiceInput.removeAttribute('readonly');
                    noInvoiceInput.style.backgroundColor = '#fff';
                    noInvoiceInput.focus();
                    iconEditNo.classList.remove('fa-pencil-alt');
                    iconEditNo.classList.add('fa-lock-open');
                } else {
                    noInvoiceInput.setAttribute('readonly', true);
                    noInvoiceInput.style.backgroundColor = '#e9ecef';
                    iconEditNo.classList.remove('fa-lock-open');
                    iconEditNo.classList.add('fa-pencil-alt');
                }
            });
        });
    </script>
@endsection
