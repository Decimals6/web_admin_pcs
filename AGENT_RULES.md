# AGENT_RULES.md - Sistem Akuntansi & Operasional Reseller

## 1. Gambaran Sistem & Model Bisnis
- **Model Bisnis:** Perusahaan reseller/trading (beli dari supplier/produsen, jual ke customer).
- **Tech Stack:** Laravel (Native/Monolith MVC), Blade Templating, AdminLTE 3 (Bootstrap 4 + FontAwesome).
- **Arsitektur:** Konvensional per modul (Controller manual per resource dengan view terpisah: `index`, `create`, `edit`).

## 2. Peta Modul & Route Prefix
- **Master Data:** `barang.*`, `users.*`, `suppliers.*`, `customers.*`, `banks.*` (CRUD standar).
- **Inventory:** `gudang.sampel.*`, `mutasi-barangs.*`.
- **Pembelian (`pembelian.*`):** `purchase-order` -> `delivery-note` (Surat Jalan) -> `invoice` (Faktur) -> `data-pembelian` & `hutang`.
- **Penjualan (`penjualan.*`):** `penawaran` -> `sales-order` -> `delivery-note` -> `invoice` -> `data-penjualan`, `piutang`, & `payment`.
- **HR / Absensi (`absensi.*`):** `absen-karyawan`, `premi-hadir`, `premi-karyawan`, `sewa-kendaraan`.
- **Keuangan / Petty Cash (`petty_cash.*`):** `kas`, `voucher`.

## 3. Aturan Inti Alur Transaksi (Core Business Rules)
- **Single Source of Truth:** Semua transaksi pembelian & penjualan **terkunci pada PO (Purchase Order) / SO (Sales Order)**.
- **Urutan Wajib:** `PO / SO` -> `Delivery Note (Surat Jalan)` -> `Invoice (Faktur)`. Invoice tidak boleh dibuat tanpa Surat Jalan, dan Surat Jalan tidak boleh dibuat tanpa PO/SO.
- **Aturan Toleransi Kuantitas & Status Saat Ini (Hasil Audit Kode):**
  - **Tidak ada batasan toleransi di Backend:** Di `DeliveryNoteController@store` (baris 144-150), logika validasi `$sisaQty = $orderDetail->qty - $orderDetail->qty_sent` saat ini **di-comment**, sehingga backend menerima `qty` berapa pun tanpa batas atas (over-delivery tidak diblokir dan tidak dicatat status selisihnya).
  - **Validasi Frontend Tidak Konsisten:**
    - `pembelian/delivery_note/create.blade.php` & `edit.blade.php`: Input `qty` **tidak memiliki atribut `max`**, sehingga user bebas mengisi kuantitas melebihi sisa PO.
    - `penjualan/delivery_note/create.blade.php`: Input `qty` memiliki `max="${detail.sisa}"` (hanya di HTML, mudah di-bypass dan tidak diverifikasi ulang di backend).
    - `penjualan/delivery_note/edit.blade.php`: Item lama yang di-load saat halaman dibuka tidak diberi atribut `max` (`detail.sisa` undefined), hanya item tambahan baru via `addAvailableRow()` yang diberi `max`.
  - **Perilaku Invoice Terhadap Kuantitas Surat Jalan:**
    - Pada `create.blade.php` (Pembelian & Penjualan), field `qty` dan `harga` di-load otomatis dari endpoint `getDeliveryNoteDetail` dalam status `readonly`. Namun, di `InvoiceController` (`storeMasuk`, `storeKeluar`, `updateMasuk`, `updateKeluar`) **tidak ada validasi backend** yang mencocokkan `details.*.qty` dengan total `qty` pada `delivery_note_ids` yang dipilih.
    - Pada `edit.blade.php` (Pembelian & Penjualan), field `qty` dan `harga` terbuka bebas untuk diedit (`min="0.01"` tanpa batas maksimal), sehingga user dapat mengubah kuantitas/harga setelah Invoice terbit tanpa sinkronisasi ke Surat Jalan maupun PO/SO.
  - **Kebijakan Wajib Ke Depan:** Setiap selisih kuantitas antara PO/SO dan Surat Jalan (misal: PO 10 pcs, datang 11 pcs) wajib divalidasi dengan batas toleransi eksplisit di backend, tercatat status selisih/toleransinya, tidak mengubah angka asli PO/SO secara diam-diam, dan Invoice wajib mengacu pada kuantitas sah dari Surat Jalan yang tertaut.
- **Integritas Data Keuangan & Stok:**
  - Wajib membungkus operasi simpan/update/hapus transaksi (Surat Jalan, Invoice, Pembayaran, Mutasi) menggunakan `DB::transaction()`.
  - Perubahan pada Invoice atau Pembayaran wajib menyinkronkan saldo `hutang` / `piutang` (`paid` & `status`) dan rekap `data-pembelian` / `data-penjualan`.

## 4. Konvensi Kode & UI
- **UI Framework:** Pertahankan struktur class AdminLTE (`nav-treeview`, `card`, `table`, dsb.) dan icon FontAwesome sesuai layout yang ada.
- **Efisiensi Konteks:** Saat mengerjakan modul baru atau bugfix, JANGAN memindai seluruh folder `app/Http/Controllers` atau `resources/views`. Cukup baca file controller, model, dan blade yang relevan dengan prefix modul tersebut.

## 5. Known Issues / Technical Debt (Hasil Audit Transaksi)

### A. Bug Kritis Kalkulasi Stok & Mutasi (Harus Segera Diperbaiki)
1. **Double Stock Mutation saat Membuat Delivery Note (`DeliveryNote.php` vs `DeliveryNoteController.php`):**
   - Model `DeliveryNote::booted()` (event `static::created`) menjalankan loop `$deliveryNote->details` untuk menambah/mengurangi `$barang->stok`, sementara `DeliveryNoteController@store` (dan `storeMasuk`) juga secara manual menambah/mengurangi `$barang->stok` setelah membuat detail.
   - *Catatan:* Saat `static::created` terpanggil di `DeliveryNote::create()`, `details` belum tersimpan (sehingga tidak jalan saat create), namun pattern ini berbahaya jika detail dibuat sebelum event atau melalui factory/seeder.
2. **Bug Fatal pada `DeliveryNoteController@updateKeluar` (Penjualan):**
   - Method `updateKeluar()` merupakan copy-paste dari `update()` (Pembelian). Saat reset stok lama, kode melakukan `$barang->stok -= $oldDetail->qty` (seharusnya `+=` untuk penjualan), lalu saat simpan baru melakukan `$barang->stok += $item['qty']` (seharusnya `-=`) dan mencatat `MutasiBarang` dengan `'tipe' => 'IN'` (seharusnya `'OUT'`). Akibatnya, setiap kali Surat Jalan Penjualan di-edit, **stok gudang justru bertambah dua kali lipat dari qty pengiriman**.
3. **Penghapusan `MutasiBarang` Terlalu Luas pada `update` & `updateKeluar`:**
   - Query `MutasiBarang::where('tgl_mutasi', $deliveryNote->tgl)->whereIn('barang_id', ...)->delete()` menghapus semua mutasi barang pada tanggal dan `barang_id` yang sama, berisiko menghapus mutasi milik Surat Jalan lain atau mutasi manual di hari yang sama karena `MutasiBarang` tidak menyimpan referensi `delivery_note_id`.
4. **`qty_sent` Tidak Di-rollback saat Delete & Ter-reset saat Edit PO/SO:**
   - `DeliveryNoteController@destroy`, `OrdersController@destroyPO`, dan `OrdersController@destroySO` mengembalikan `$barang->stok`, tetapi **lupa mengurangi `$orderDetail->qty_sent`**. Akibatnya sisa kuota kirim di PO/SO tetap terpakai meski Surat Jalan sudah dihapus.
   - `OrdersController@updatePO` dan `updateSO` menghapus seluruh `OrderDetail` lama dan membuat ulang dengan `'qty_sent' => 0`, sehingga riwayat pengiriman parsial hilang dan `DeliveryNoteDetail.order_detail_id` menjadi *orphan* (putus relasi).
5. **Inkonsistensi `qty` pada `DeliveryNoteController@getDeliveryNoteDetail` & `storeMasuk`:**
   - `DeliveryNoteController@getDeliveryNoteDetail` mengembalikan `'qty' => $detail->orderDetail->qty` (kuantitas PO/SO), bukan `$detail->qty` (kuantitas aktual Surat Jalan). *(Route aktif `/pembelian/delivery-note/{id}/details` saat ini mengarah ke `InvoiceController@getDeliveryNoteDetail` yang sudah memakai `$d->qty`, namun method di `DeliveryNoteController` masih keliru).*

### B. Celah Validasi & Integritas Alur Transaksi
1. **Over-Delivery Tidak Terkontrol di Backend (`DeliveryNoteController@store`, `update`, `updateKeluar`):**
   - Validasi sisa kuantitas PO/SO di-comment. Tidak ada pengecekan apakah total `qty` seluruh Surat Jalan untuk suatu `order_detail_id` melebihi `order_details.qty` (atau batas toleransi yang diizinkan).
   - `DeliveryNoteController@store`, `update`, `updateKeluar` juga belum dibungkus `DB::transaction()`.
   - `order_id` divalidasi `nullable|exists:orders,id` (bertentangan dengan aturan wajib bahwa Surat Jalan harus terikat pada PO/SO).
2. **Validasi Nominal & Kuantitas Invoice Terbuka Bebas (`InvoiceController`):**
   - `storeMasuk`, `storeKeluar`, `updateMasuk`, dan `updateKeluar` tidak memvalidasi bahwa `details.*.qty` <= total `qty` dari `delivery_note_ids` yang dipilih, maupun apakah Surat Jalan tersebut sudah pernah dibuatkan Invoice sebelumnya (potensi *double invoicing* untuk 1 Surat Jalan).
   - Tidak ada proteksi/pembatasan edit maupun delete terhadap Invoice yang sudah berstatus `partial` atau `paid` (sudah ada pembayaran masuk/keluar).
   - Pada `updateKeluar`, validasi `'no' => 'required|unique:invoices,no,' . $id` hilang (berbeda dengan `updateMasuk` yang memilikinya).

### C. Bug Routing, Query & Saldo Hutang/Piutang
1. **Salah Target Method pada Route Delete Invoice Pembelian (`routes/web.php`):**
   - `Route::delete('/pembelian/invoice/{id}', [InvoiceController::class, 'destroyKeluar'])` memanggil `destroyKeluar` alih-alih `destroyMasuk` (walaupun isi method-nya identik, rawan bug saat logika dipisah).
2. **Redirect Salah pada `OrdersController@destroyPO`:**
   - Setelah menghapus PO, `destroyPO` melakukan `redirect()->route('pembelian.sales-order.index')` (nama route tidak valid / salah modul, seharusnya `pembelian.purchase-order.index`).
3. **Ketidakkonsistenan Perhitungan Ongkir pada Hutang vs Piutang (`InvoiceController`):**
   - `bayarHutang()` menghitung tagihan dengan `$inv->grand_total + $inv->ongkirs->sum('nominal')`, padahal `storeMasuk()` menyimpan ongkir di kolom `invoices.ongkir` yang sudah masuk ke `grand_total` (tidak di tabel `invoice_ongkirs`). Sebaliknya, `getHutangDetail()` hanya memakai `$inv->grand_total`.
   - `bayarPiutang()` menghitung `$inv->grand_total_with_ongkir` (`grand_total + ongkirs->sum('nominal')`), sedangkan `getPiutangDetail()` hanya menampilkan `$inv->grand_total` dan meng-comment loop `ongkirs`, sehingga detail tagihan ongkir tidak tampil di rincian piutang namun ikut ditagih saat pelunasan.
   - `InvoiceController@exportPembelianpdf` memuat relasi `'customer'` pada query `Invoice::TYPE_MASUK` (seharusnya `'supplier'`).