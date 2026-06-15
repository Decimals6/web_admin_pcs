@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Edit Invoice Penjualan</h4>
        </div>

        <form action="{{ route('penjualan.invoice.update', $invoice->id) }}" method="POST" id="invoiceForm">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    <strong>Penyimpanan Gagal!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-2">
                        <label>No. Invoice</label>
                        <div class="input-group">
                            <input type="text" name="no" id="no_invoice" class="form-control" value="{{ $invoice->no }}"
                                readonly style="background-color: #e9ecef;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-toggle-invoice"
                                title="Edit Nomor Invoice">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal Invoice</label>
                        <input type="date" name="tgl" class="form-control"
                            value="{{ \Carbon\Carbon::parse($invoice->tgl)->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <input type="text" name="customer_name" class="form-control" id="customer_name"
                            value="{{ $invoice->customer->nama_customer ?? '' }}" readonly
                            style="background-color:#e9ecef;">
                    </div>

                    <div class="col-md-3">
                        <label>Delivery Note</label>
                        <div class="input-group">
                            <select id="delivery_note_select" class="form-control">
                                <option value="">-- Pilih Delivery Note --</option>
                                @foreach($deliveryNotes as $dn)
                                    <option value="{{ $dn->id }}">
                                        {{ $dn->no }} | {{ $dn->tgl->format('d-m-Y') }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="add_dn">+ Add</button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tgl Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" class="form-control"
                            value="{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="mt-3">
                    <div id="selected_dn" class="border rounded p-2 bg-light" style="min-height:40px;">
                        @foreach($invoice->deliveryNote as $dnOld)
                            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-white">
                                <span>{{ $dnOld->no }} | {{ $dnOld->tgl->format('d-m-Y') }}</span>
                                <button type="button" class="btn btn-sm btn-danger remove-dn"
                                    data-id="{{ $dnOld->id }}">Hapus</button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="hidden_dn_inputs">
                    @foreach($invoice->deliveryNote as $dnOld)
                        <input type="hidden" name="delivery_note_ids[]" value="{{ $dnOld->id }}" data-id="{{ $dnOld->id }}">
                    @endforeach
                </div>

                <hr>
                <h5>Detail Barang</h5>
                <table class="table table-bordered" id="items-table">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th>Barang</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 20%;">Harga</th>
                            <th style="width: 20%;">Subtotal</th>
                            <th style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->details as $index => $detail)
                            <tr>
                                <td>
                                    {{ $detail->orderDetail->barang->nama_barang ?? 'Unknown' }}
                                    <input type="hidden" name="details[{{ $index }}][barang_id]"
                                        value="{{ $detail->orderDetail->barang_id }}">
                                    <input type="hidden" name="details[{{ $index }}][order_detail_id]"
                                        value="{{ $detail->order_detail_id }}">
                                </td>
                                <td>
                                    <input type="number" name="details[{{ $index }}][qty]" class="form-control qty"
                                        value="{{ $detail->qty }}" min="0.01" step="any" required>
                                </td>
                                <td>
                                    <input type="number" name="details[{{ $index }}][harga]" class="form-control harga"
                                        value="{{ $detail->orderDetail->harga ?? 0 }}" min="0" step="0.01" required>
                                </td>
                                <td>
                                    <input type="number" name="details[{{ $index }}][subtotal]"
                                        class="form-control subtotal-detail" value="{{ $detail->subtotal }}" readonly
                                        style="background-color:#e9ecef;">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm remove-row">-</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Pajak</label>
                        <select name="ppn_mode" id="ppn_mode" class="form-control">
                            <option value="ppn" {{ $invoice->ppn > 0 ? 'selected' : '' }}>PPN 11%</option>
                            <option value="non" {{ $invoice->ppn == 0 ? 'selected' : '' }}>Non PPN</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Nominal Ongkir</label>
                        <input type="number" name="ongkir" id="ongkir" class="form-control"
                            value="{{ $invoice->ongkir ?? 0 }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label>Diskon Potongan</label>
                        <input type="number" name="diskon" id="diskon" class="form-control"
                            value="{{ $invoice->diskon ?? 0 }}" min="0" step="0.01">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <label>Subtotal (DPP)</label>
                        <input type="number" name="dpp" id="dpp" class="form-control" value="{{ $invoice->dpp }}" readonly
                            style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Pajak 11%</label>
                        <input type="number" name="pajak" id="pajak" class="form-control" value="{{ $invoice->ppn }}"
                            readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Grand Total</label>
                        <input type="number" name="total" id="total" class="form-control"
                            value="{{ $invoice->grand_total }}" readonly style="background-color:#e9ecef;">
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control" value="{{ $invoice->status }}" readonly
                            style="background-color:#e9ecef;">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-success" type="submit">Perbarui Invoice</button>
                <a href="{{ route('penjualan.invoice.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const itemsTable = document.querySelector('#items-table tbody');
            const customerInput = document.getElementById('customer_name');
            const ppnMode = document.getElementById('ppn_mode');
            const diskonInput = document.getElementById('diskon');
            const ongkirInput = document.getElementById('ongkir');

            const dnSelect = document.getElementById('delivery_note_select');
            const addDnBtn = document.getElementById('add_dn');
            const selectedDnBox = document.getElementById('selected_dn');
            const hiddenContainer = document.getElementById('hidden_dn_inputs');

            let rowIndex = {{ $invoice->details->count() }};
            let selectedDN = [];

            @foreach($invoice->deliveryNote as $dnOld)
                selectedDN.push("{{ $dnOld->id }}");
                let opt = dnSelect.querySelector(`option[value="${{ $dnOld->id }}"]`);
                if (opt) opt.style.display = 'none';
            @endforeach

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

                let diskon = parseFloat(diskonInput.value) || 0;
                let ongkir = parseFloat(ongkirInput.value) || 0;

                let subtotalSetelahDiskon = dpp - diskon;
                if (subtotalSetelahDiskon < 0) subtotalSetelahDiskon = 0;

                let mode = ppnMode ? ppnMode.value : 'ppn';
                let pajak = (mode === 'ppn') ? (subtotalSetelahDiskon * 0.11) : 0;
                let grandTotal = subtotalSetelahDiskon + pajak + ongkir;

                document.getElementById('dpp').value = dpp.toFixed(2);
                document.getElementById('pajak').value = pajak.toFixed(2);
                document.getElementById('total').value = grandTotal.toFixed(2);
            }

            if (ppnMode) ppnMode.addEventListener('change', calculateTotal);
            if (diskonInput) diskonInput.addEventListener('input', calculateTotal);
            if (ongkirInput) ongkirInput.addEventListener('input', calculateTotal);

            addDnBtn.addEventListener('click', function () {
                const dnId = dnSelect.value;
                if (!dnId) { alert('Pilih Delivery Note dulu'); return; }
                if (selectedDN.includes(dnId)) { alert('Delivery Note sudah ditambahkan'); return; }

                const dnText = dnSelect.options[dnSelect.selectedIndex].text;
                if (selectedDN.length === 0) { selectedDnBox.innerHTML = ''; }
                selectedDN.push(dnId);

                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'delivery_note_ids[]';
                hidden.value = dnId;
                hidden.setAttribute('data-id', dnId);
                hiddenContainer.appendChild(hidden);

                dnSelect.querySelector(`option[value="${dnId}"]`).style.display = 'none';

                let dnDiv = document.createElement('div');
                dnDiv.className = 'd-flex justify-content-between align-items-center border rounded p-2 mb-2 bg-white';
                dnDiv.innerHTML = `
                        <span>${dnText}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-dn" data-id="${dnId}">Hapus</button>
                    `;
                selectedDnBox.appendChild(dnDiv);
                dnSelect.value = '';

                loadDN(dnId);
            });

            function loadDN(dnId) {
                fetch(`/penjualan/delivery-note/${dnId}/details`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) return;
                        if (!customerInput.value) {
                            customerInput.value = data[0].customer_name || '';
                        }

                        data.forEach(item => {
                            let row = document.createElement('tr');
                            row.innerHTML = `
                                    <td>
                                        ${item.nama_barang}
                                        <input type="hidden" name="details[${rowIndex}][barang_id]" value="${item.barang_id}">
                                        <input type="hidden" name="details[${rowIndex}][order_detail_id]" value="${item.order_detail_id}">
                                    </td>
                                    <td><input type="number" name="details[${rowIndex}][qty]" class="form-control qty" value="${item.qty}" min="0.01" step="any" required></td>
                                    <td><input type="number" name="details[${rowIndex}][harga]" class="form-control harga" value="${item.harga}" min="0" step="0.01" required></td>
                                    <td><input type="number" name="details[${rowIndex}][subtotal]" class="form-control subtotal-detail" readonly style="background-color:#e9ecef;"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">-</button></td>
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

                    let opt = dnSelect.querySelector(`option[value="${dnId}"]`);
                    if (opt) opt.style.display = 'block';

                    let targetHidden = hiddenContainer.querySelector(`input[data-id="${dnId}"]`);
                    if (targetHidden) targetHidden.remove();

                    e.target.closest('div').remove();

                    itemsTable.innerHTML = '';
                    rowIndex = 0;
                    selectedDN.forEach(loadDN);

                    if (selectedDN.length === 0) {
                        customerInput.value = '';
                        selectedDnBox.innerHTML = `<small class="text-muted">Delivery Note dipilih akan muncul di sini</small>`;
                        calculateTotal();
                    }
                }
            });

            itemsTable.addEventListener('input', function (e) {
                if (e.target.classList.contains('qty') || e.target.classList.contains('harga')) {
                    let row = e.target.closest('tr');
                    calculateRow(row);
                }
            });

            itemsTable.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                    calculateTotal();
                }
            });

            // === BARIS PERBAIKAN: Logic Tombol Kunci/Buka Nomor Invoice ===
            const inputInvoice = document.getElementById("no_invoice");
            const btnToggleInvoice = document.getElementById("btn-toggle-invoice");

            if (inputInvoice && btnToggleInvoice) {
                btnToggleInvoice.addEventListener("click", function () {
                    if (inputInvoice.hasAttribute("readonly")) {
                        inputInvoice.removeAttribute("readonly");
                        inputInvoice.style.backgroundColor = "#fff";
                        btnToggleInvoice.classList.remove("btn-outline-secondary");
                        btnToggleInvoice.classList.add("btn-danger");
                        btnToggleInvoice.innerHTML = '<i class="fas fa-lock"></i>';
                        inputInvoice.focus();
                    } else {
                        inputInvoice.setAttribute("readonly", true);
                        inputInvoice.style.backgroundColor = "#e9ecef";
                        btnToggleInvoice.classList.remove("btn-danger");
                        btnToggleInvoice.classList.add("btn-outline-secondary");
                        btnToggleInvoice.innerHTML = '<i class="fas fa-pencil-alt"></i>';
                    }
                });
            }
        });
    </script>
@endsection