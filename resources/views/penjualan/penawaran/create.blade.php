@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4>Buat Penawaran</h4>
        </div>

        <form action="{{ route('penjualan.penawaran.store') }}" method="POST" id="penawaranForm">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger m-3">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-3">
                        <label>No. Penawaran</label>
                        <div class="input-group">
                            <input type="text" name="no_penawaran" id="no_penawaran" class="form-control"
                                value="{{ $noPenawaran }}" readonly style="background-color: #e9ecef;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-toggle-no"
                                title="Edit Nomor Penawaran">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-2">
                        <label>Berlaku Hingga</label>
                        <input type="date" name="berlaku_hingga" class="form-control"
                            value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>

                    <div class="col-md-3">
                        <label>Customer</label>
                        <select name="customer_id" class="form-control" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customer as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_customer }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Up (PIC Customer)</label>
                        <input type="text" name="up" class="form-control" placeholder="cth: Ibu Lucia">
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <label>Hal / Perihal</label>
                        <input type="text" name="perihal" class="form-control"
                            placeholder="cth: Kebutuhan Label Semicoated" required>
                    </div>

                    <div class="col-md-6">
                        <label>Catatan</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Catatan internal (opsional)">
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Detail Barang</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="add-item">+ Tambah Barang</button>
                </div>

                <div id="items-container">
                    {{-- Item rows akan di-clone dari template lewat JS --}}
                </div>

            </div>

            <div class="card-footer">
                <button class="btn btn-success">Simpan Penawaran</button>
                <a href="{{ route('penjualan.penawaran.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>

    {{-- ============================================================ --}}
    {{-- TEMPLATE: 1 ITEM BARANG (di-clone oleh JS, tidak ditampilkan langsung) --}}
    {{-- ============================================================ --}}
    <template id="template-item">
        <div class="card mb-3 item-card">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <strong>Barang <span class="item-number"></span></strong>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="fas fa-trash"></i> Hapus Barang
                </button>
            </div>
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Tipe Barang</label>
                        <select class="form-control input-tipe" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="consumable">Barang Habis Pakai (Consumable)</option>
                            <option value="equipment">Barang Besar / Equipment</option>
                        </select>
                    </div>

                    <div class="col-md-5 select-barang-wrapper" style="display:none;">
                        <label>Pilih Barang</label>
                        <select class="form-control input-barang" required>
                            <option value="">-- Pilih Barang --</option>
                            @foreach($barangs as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 satuan-wrapper" style="display:none;">
                        <label>Satuan</label>
                        <input type="text" class="form-control input-satuan" placeholder="pcs, roll, unit...">
                    </div>
                </div>

                <div class="item-body" style="display:none;">

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label>Keterangan Tambahan (opsional)</label>
                            <input type="text" class="form-control input-keterangan" placeholder="Catatan bebas untuk item ini">
                        </div>
                    </div>

                    {{-- ===================== SPESIFIKASI ===================== --}}
                    <div class="border rounded p-3 mb-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Spesifikasi Barang</strong>
                            <button type="button" class="btn btn-sm btn-outline-primary add-spek">
                                + Tambah Spek
                            </button>
                        </div>
                        <div class="spek-container">
                            {{-- spek rows di-clone di sini --}}
                        </div>
                        <small class="text-muted spek-empty-hint">Belum ada spesifikasi ditambahkan.</small>
                    </div>

                    {{-- ===================== HARGA / TIER ===================== --}}
                    <div class="border rounded p-3 mb-2 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Harga <span class="harga-label-tipe text-muted"></span></strong>
                            <button type="button" class="btn btn-sm btn-outline-success add-harga">
                                + Tambah Tier Harga
                            </button>
                        </div>
                        <div class="harga-container">
                            {{-- harga rows di-clone di sini --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </template>

    {{-- TEMPLATE: 1 ROW SPESIFIKASI --}}
    <template id="template-spek">
        <div class="row mb-2 align-items-center spek-row">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm input-nama-spek" placeholder="Nama Spek (cth: Bahan)">
            </div>
            <div class="col-md-7">
                <input type="text" class="form-control form-control-sm input-isi-spek" placeholder="Isi (cth: Semicoated)">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-spek w-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </template>

    {{-- TEMPLATE: 1 ROW HARGA (CONSUMABLE - dengan min qty) --}}
    <template id="template-harga-consumable">
        <div class="row mb-2 align-items-center harga-row">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Min. Qty</span>
                    <input type="number" class="form-control input-min-qty" min="1" placeholder="cth: 100">
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Harga</span>
                    <input type="number" class="form-control input-harga-nilai" min="0" placeholder="cth: 10000">
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-harga w-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </template>

    {{-- TEMPLATE: 1 ROW HARGA (EQUIPMENT - flat, tanpa min qty) --}}
    <template id="template-harga-equipment">
        <div class="row mb-2 align-items-center harga-row">
            <div class="col-md-11">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Harga Satuan</span>
                    <input type="number" class="form-control input-harga-nilai" min="0" placeholder="cth: 5000000">
                </div>
            </div>
            <div class="col-md-1">
                {{-- equipment tidak bisa hapus baris harga (cuma 1 baris) --}}
            </div>
        </div>
    </template>


@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const itemsContainer = document.getElementById('items-container');
    const templateItem = document.getElementById('template-item');
    const templateSpek = document.getElementById('template-spek');
    const templateHargaConsumable = document.getElementById('template-harga-consumable');
    const templateHargaEquipment = document.getElementById('template-harga-equipment');

    let itemIndex = 0;

    // ============================================================
    // TAMBAH ITEM BARANG BARU
    // ============================================================
    function addItem() {
        const clone = templateItem.content.cloneNode(true);
        const itemCard = clone.querySelector('.item-card');
        itemCard.dataset.index = itemIndex;
        clone.querySelector('.item-number').textContent = itemIndex + 1;

        itemsContainer.appendChild(clone);
        itemIndex++;
        renumberItems();
    }

    function renumberItems() {
        document.querySelectorAll('.item-card').forEach((card, i) => {
            card.querySelector('.item-number').textContent = i + 1;
        });
    }

    document.getElementById('add-item').addEventListener('click', addItem);

    // Mulai dengan 1 item kosong biar user langsung bisa isi
    addItem();

    // ============================================================
    // EVENT DELEGATION - semua interaksi di dalam items-container
    // ============================================================
    itemsContainer.addEventListener('change', function (e) {

        // --- Saat TIPE barang dipilih ---
        if (e.target.classList.contains('input-tipe')) {
            const card = e.target.closest('.item-card');
            const tipe = e.target.value;
            const selectBarangWrapper = card.querySelector('.select-barang-wrapper');
            const satuanWrapper = card.querySelector('.satuan-wrapper');
            const itemBody = card.querySelector('.item-body');
            const hargaContainer = card.querySelector('.harga-container');
            const hargaLabel = card.querySelector('.harga-label-tipe');

            if (tipe) {
                selectBarangWrapper.style.display = '';
                satuanWrapper.style.display = '';
            } else {
                selectBarangWrapper.style.display = 'none';
                satuanWrapper.style.display = 'none';
                itemBody.style.display = 'none';
            }

            // Reset harga container sesuai tipe baru
            hargaContainer.innerHTML = '';

            if (tipe === 'consumable') {
                hargaLabel.textContent = '(Multi-Tier berdasarkan Qty)';
                addHargaRow(card, 'consumable');
            } else if (tipe === 'equipment') {
                hargaLabel.textContent = '(Harga Flat per Unit)';
                addHargaRow(card, 'equipment');
            }
        }

        // --- Saat BARANG dipilih, tampilkan body item (spek + harga) ---
        if (e.target.classList.contains('input-barang')) {
            const card = e.target.closest('.item-card');
            const itemBody = card.querySelector('.item-body');
            if (e.target.value) {
                itemBody.style.display = '';
            } else {
                itemBody.style.display = 'none';
            }
        }
    });

    itemsContainer.addEventListener('click', function (e) {

        // --- HAPUS ITEM BARANG ---
        if (e.target.closest('.remove-item')) {
            if (document.querySelectorAll('.item-card').length > 1) {
                e.target.closest('.item-card').remove();
                renumberItems();
            } else {
                alert('Minimal harus ada 1 barang dalam penawaran.');
            }
        }

        // --- TAMBAH SPEK ---
        if (e.target.closest('.add-spek')) {
            const card = e.target.closest('.item-card');
            addSpekRow(card);
        }

        // --- HAPUS SPEK ---
        if (e.target.closest('.remove-spek')) {
            const card = e.target.closest('.item-card');
            e.target.closest('.spek-row').remove();
            toggleSpekHint(card);
        }

        // --- TAMBAH TIER HARGA (hanya untuk consumable) ---
        if (e.target.closest('.add-harga')) {
            const card = e.target.closest('.item-card');
            const tipe = card.querySelector('.input-tipe').value;
            if (tipe === 'consumable') {
                addHargaRow(card, 'consumable');
            } else {
                alert('Barang Equipment hanya memiliki 1 harga flat. Tidak bisa menambah tier.');
            }
        }

        // --- HAPUS TIER HARGA ---
        if (e.target.closest('.remove-harga')) {
            const card = e.target.closest('.item-card');
            const hargaContainer = card.querySelector('.harga-container');
            if (hargaContainer.querySelectorAll('.harga-row').length > 1) {
                e.target.closest('.harga-row').remove();
            } else {
                alert('Minimal harus ada 1 baris harga.');
            }
        }
    });

    // ============================================================
    // HELPER: Tambah baris spesifikasi
    // ============================================================
    function addSpekRow(card) {
        const clone = templateSpek.content.cloneNode(true);
        card.querySelector('.spek-container').appendChild(clone);
        toggleSpekHint(card);
    }

    function toggleSpekHint(card) {
        const hint = card.querySelector('.spek-empty-hint');
        const count = card.querySelectorAll('.spek-row').length;
        hint.style.display = count > 0 ? 'none' : '';
    }

    // ============================================================
    // HELPER: Tambah baris harga, sesuai tipe (consumable/equipment)
    // ============================================================
    function addHargaRow(card, tipe) {
        const template = tipe === 'equipment' ? templateHargaEquipment : templateHargaConsumable;
        const clone = template.content.cloneNode(true);
        card.querySelector('.harga-container').appendChild(clone);
    }

    // ============================================================
    // TOGGLE EDIT NOMOR PENAWARAN (sama seperti pola di SO)
    // ============================================================
    const inputNo = document.getElementById('no_penawaran');
    const btnToggleNo = document.getElementById('btn-toggle-no');

    btnToggleNo.addEventListener('click', function () {
        if (inputNo.hasAttribute('readonly')) {
            inputNo.removeAttribute('readonly');
            inputNo.style.backgroundColor = '#fff';
            btnToggleNo.classList.remove('btn-outline-secondary');
            btnToggleNo.classList.add('btn-danger');
            btnToggleNo.innerHTML = '<i class="fas fa-lock"></i>';
            inputNo.focus();
        } else {
            inputNo.setAttribute('readonly', true);
            inputNo.style.backgroundColor = '#e9ecef';
            btnToggleNo.classList.remove('btn-danger');
            btnToggleNo.classList.add('btn-outline-secondary');
            btnToggleNo.innerHTML = '<i class="fas fa-pencil-alt"></i>';
        }
    });

    // ============================================================
    // SUBMIT HANDLER: Rakit semua data jadi input hidden ber-nama
    // "item[i][...]" sebelum form di-submit, sesuai struktur
    // yang divalidasi controller (item.*.spek.*, item.*.harga.*)
    // ============================================================
    document.getElementById('penawaranForm').addEventListener('submit', function (e) {
        const hiddenContainer = document.createElement('div');
        hiddenContainer.style.display = 'none';

        const itemCards = document.querySelectorAll('.item-card');

        if (itemCards.length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal 1 barang.');
            return;
        }

        itemCards.forEach((card, i) => {
            const tipe = card.querySelector('.input-tipe').value;
            const barangId = card.querySelector('.input-barang').value;
            const satuan = card.querySelector('.input-satuan').value;
            const keterangan = card.querySelector('.input-keterangan').value;

            addHidden(hiddenContainer, `item[${i}][tipe]`, tipe);
            addHidden(hiddenContainer, `item[${i}][barang_id]`, barangId);
            addHidden(hiddenContainer, `item[${i}][satuan]`, satuan);
            addHidden(hiddenContainer, `item[${i}][keterangan]`, keterangan);

            // Spek
            card.querySelectorAll('.spek-row').forEach((row, j) => {
                const namaSpek = row.querySelector('.input-nama-spek').value;
                const isiSpek = row.querySelector('.input-isi-spek').value;
                if (namaSpek && isiSpek) {
                    addHidden(hiddenContainer, `item[${i}][spek][${j}][nama_spek]`, namaSpek);
                    addHidden(hiddenContainer, `item[${i}][spek][${j}][keterangan]`, isiSpek);
                }
            });

            // Harga
            card.querySelectorAll('.harga-row').forEach((row, j) => {
                const hargaNilai = row.querySelector('.input-harga-nilai').value;
                const minQtyInput = row.querySelector('.input-min-qty');
                const minQty = tipe === 'equipment' ? 1 : (minQtyInput ? minQtyInput.value : 1);

                addHidden(hiddenContainer, `item[${i}][harga][${j}][min_qty]`, minQty);
                addHidden(hiddenContainer, `item[${i}][harga][${j}][harga]`, hargaNilai);
            });
        });

        this.appendChild(hiddenContainer);
    });

    function addHidden(container, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value ?? '';
        container.appendChild(input);
    }

});
</script>
@endsection