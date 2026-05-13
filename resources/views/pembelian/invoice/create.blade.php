@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Buat Invoice Pembelian</h4>
        </div>

        <form action="{{ route('pembelian.invoice.store') }}" method="POST" id="invoiceForm">
            @csrf
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-2">
                        <label>No. Invoice</label>
                        <input type="text" name="no" class="form-control" required>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal Invoice</label>
                        <input type="date" name="tgl" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Supplier</label>
                        <input type="text" name="supplier_name" class="form-control" id="supplier_name" readonly
                            style="background-color:#e9ecef;">
                    </div>

                    <div class="col-md-3">
                        <label>Delivery Note</label>

                        <div class="d-flex">
                            <select id="delivery_note_select" class="form-control">
                                <option value="">-- Pilih Delivery Note --</option>
                                @foreach($deliveryNotes as $dn)
                                    <option value="{{ $dn->id }}">
                                        {{ $dn->no }} | {{ $dn->tgl->format('d-m-Y') }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="button" class="btn btn-primary ml-2" id="add_dn">
                                Add
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tgl Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mt-3">
                    <div id="selected_dn" class="border rounded p-2 bg-light" style="min-height:40px;">
                        <small class="text-muted">Delivery Note dipilih akan muncul di sini</small>
                    </div>
                </div>
                <hr>
                <h5>Detail Barang</h5>
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row akan diisi otomatis via JS -->
                    </tbody>
                </table>

                <hr>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Pajak</label>
                        <select name="ppn_mode" id="ppn_mode" class="form-control">
                            <option value="ppn" selected>PPN 11%</option>
                            <option value="non">Non PPN</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>Subtotal (DPP)</label>
                        <input type="number" name="dpp" id="dpp" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Pajak 11%</label>
                        <input type="number" name="pajak" id="pajak" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Total</label>
                        <input type="number" name="total" id="total" class="form-control" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control" value="unpaid" readonly
                            style="background-color:#e9ecef;">
                    </div>
                </div>

            </div>

            <div class="card-footer">
                <button class="btn btn-success">Simpan Invoice</button>
                <a href="{{ route('pembelian.invoice.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const itemsTable = document.querySelector('#items-table tbody');
            const supplierInput = document.getElementById('supplier_name');

            const dnSelect = document.getElementById('delivery_note_select');
            const addDnBtn = document.getElementById('add_dn');
            const selectedDnBox = document.getElementById('selected_dn');
            let placeholder = selectedDnBox.querySelector('small');

            const ppnMode = document.getElementById('ppn_mode');

            let rowIndex = 0;
            let selectedDN = [];

            function calculateRow(row) {

                let qty = parseFloat(row.querySelector('.qty').value) || 0;
                let harga = parseFloat(row.querySelector('.harga').value) || 0;

                row.querySelector('.subtotal-detail').value = (qty * harga).toFixed(2);

                calculateTotal();
            }

            function calculateTotal() {

                let dpp = 0;

                document.querySelectorAll('.subtotal-detail').forEach(input => {
                    dpp += parseFloat(input.value) || 0;
                });

                let mode = ppnMode.value;

                let pajak = 0;

                if (mode === 'ppn') {
                    pajak = dpp * 0.11;
                }

                let total = dpp + pajak;

                document.getElementById('dpp').value = dpp.toFixed(2);
                document.getElementById('pajak').value = pajak.toFixed(2);
                document.getElementById('total').value = total.toFixed(2);
            }

            ppnMode.addEventListener('change', calculateTotal);

            addDnBtn.addEventListener('click', function () {

                const dnId = dnSelect.value;

                if (!dnId) {
                    alert('Pilih Delivery Note dulu');
                    return;
                }

                if (selectedDN.includes(dnId)) {
                    alert('Delivery Note sudah dipilih');
                    return;
                }

                const dnText = dnSelect.options[dnSelect.selectedIndex].text;

                selectedDN.push(dnId);
                if (selectedDN.length === 1) {
                    selectedDnBox.innerHTML = '';
                }
                // hidden input
                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'delivery_note_ids[]';
                hidden.value = dnId;

                document.getElementById('invoiceForm').appendChild(hidden);

                // sembunyikan dari dropdown
                dnSelect.querySelector(`option[value="${dnId}"]`).style.display = 'none';

                // tampilkan list
                let div = document.createElement('div');

                div.className = 'd-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mb-1';

                div.innerHTML = `
                    <span>${dnText}</span>
                    <button type="button" class="btn btn-sm btn-danger remove-dn" data-id="${dnId}">
                        Hapus
                    </button>
                `;

                selectedDnBox.appendChild(div);

                dnSelect.value = "";

                loadDN(dnId);

            });

            function loadDN(dnId) {

                fetch(`/pembelian/delivery-note/${dnId}/details`)
                    .then(res => res.json())
                    .then(data => {

                        if (data.length === 0) return;

                        if (!supplierInput.value) {
                            supplierInput.value = data[0].supplier_name;
                        }

                        data.forEach(item => {

                            let row = document.createElement('tr');

                            row.innerHTML = `
                            <td>
                                ${item.nama_barang}
                                <input type="hidden" name="details[${rowIndex}][barang_id]" value="${item.barang_id}">
                                <input type="hidden" name="details[${rowIndex}][order_detail_id]" value="${item.order_detail_id}">
                            </td>

                            <td>
                                <input type="number" name="details[${rowIndex}][qty]" class="form-control qty" value="${item.qty}" readonly>
                            </td>

                            <td>
                                <input type="number" name="details[${rowIndex}][harga]" class="form-control harga"
                                value="${item.harga}" readonly style="background-color:#e9ecef;">
                            </td>

                            <td>
                                <input type="number" name="details[${rowIndex}][subtotal]"
                                class="form-control subtotal-detail" readonly style="background-color:#e9ecef;">
                            </td>

                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row">-</button>
                            </td>
                        `;

                            itemsTable.appendChild(row);

                            calculateRow(row);

                            rowIndex++;

                        });

                        calculateTotal();

                    });

            }

            selectedDnBox.addEventListener('click', function (e) {

                if (e.target.classList.contains('remove-dn')) {

                    const dnId = e.target.dataset.id;

                    selectedDN = selectedDN.filter(id => id !== dnId);

                    // tampilkan lagi dropdown
                    dnSelect.querySelector(`option[value="${dnId}"]`).style.display = 'block';

                    e.target.closest('div').remove();

                    // reload tabel
                    itemsTable.innerHTML = '';
                    rowIndex = 0;

                    selectedDN.forEach(loadDN);

                    if (selectedDN.length === 0) {

                        supplierInput.value = '';

                        selectedDnBox.innerHTML = `
                                        <small class="text-muted">
                                            Delivery Note dipilih akan muncul di sini
                                        </small>
                                    `;
                    }

                }

            });

            itemsTable.addEventListener('click', function (e) {

                if (e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                    calculateTotal();
                }

            });

        });
    </script>
@endsection