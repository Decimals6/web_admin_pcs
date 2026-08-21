<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\BarangHargaController;
use App\Http\Controllers\PremiUserController;
use App\Http\Controllers\SewaKendaraanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\IncomingBarangController;
use App\Http\Controllers\MutasiBarangController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\SampelController;
use App\Http\Controllers\PenawaranController;

/*
|--------------------------------------------------------------------------
| BASIC
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'));
// Route::get('/dashboard', fn() => view('dashboard'))
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('barang', BarangController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('banks', BankController::class);
    Route::resource('incoming-barangs', IncomingBarangController::class);
    Route::resource('mutasi-barangs', MutasiBarangController::class);
    Route::resource('orders', OrdersController::class);
    Route::resource('delivery-notes', DeliveryNoteController::class);
    Route::resource('payments', PaymentController::class)->only(['index', 'store', 'show', 'destroy']);
});

/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



/*
|--------------------------------------------------------------------------
| PURCHASE ORDER
|--------------------------------------------------------------------------
*/

Route::prefix('pembelian/purchase-order')
    ->name('pembelian.purchase-order.')
    ->group(function () {
        Route::get('/', [OrdersController::class, 'indexPO'])->name('index');
        Route::get('/create', [OrdersController::class, 'createPO'])->name('create');
        Route::post('/store', [OrdersController::class, 'storePO'])->name('store');

        // TAMBAHIN /{id} DI SINI, Do!
        Route::get('/{id}/edit', [OrdersController::class, 'editPO'])->name('edit');

        // Pakai PUT/PATCH untuk update data lama, sertakan juga /{id}
        Route::put('/{id}/update', [OrdersController::class, 'updatePO'])->name('update');

        // Jalur untuk nampilin detail via AJAX (opsional, sesuaikan nama fungsi di controller)
        Route::get('/{id}/detail', [OrdersController::class, 'showPODetail'])->name('detail');

        Route::get('/{id}/check-relations', [OrdersController::class, 'checkRelationsPO'])->name('check-relations');
        Route::delete('/{id}', [OrdersController::class, 'destroyPO'])->name('destroy');
    });

Route::get(
    '/pembelian/purchase-order/{id}/detail',
    [OrdersController::class, 'detail']
)
    ->name('pembelian.purchase-order.detail');

Route::get('/po/{id}', [OrdersController::class, 'showDetailPO']);

/*
|--------------------------------------------------------------------------
| Penawaran
|--------------------------------------------------------------------------
*/

Route::prefix('penjualan/penawaran')
    ->name('penjualan.penawaran.')
    ->group(function () {
        Route::get('/', [PenawaranController::class, 'index'])->name('index');
        Route::get('/create', [PenawaranController::class, 'create'])->name('create');
        Route::post('/store', [PenawaranController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PenawaranController::class, 'edit'])->name('edit');
        Route::put('/{id}/update', [PenawaranController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [PenawaranController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [PenawaranController::class, 'print'])->name('print');
    });

// Detail (dipakai untuk modal AJAX, sama pola seperti SO)
Route::get(
    '/penjualan/penawaran/{id}/detail',
    [PenawaranController::class, 'detailPenawaran']
)
    ->name('penjualan.penawaran.detail');
Route::get('/penawaran/{id}', [PenawaranController::class, 'showDetailPenawaran']);

/*
|--------------------------------------------------------------------------
| SALES ORDER
|--------------------------------------------------------------------------
*/

Route::prefix('penjualan/sales-order')
    ->name('penjualan.sales-order.')
    ->group(function () {
        Route::get('/', [OrdersController::class, 'indexSO'])->name('index');
        Route::get('/create', [OrdersController::class, 'createSO'])->name('create');
        Route::post('/store', [OrdersController::class, 'storeSO'])->name('store');
        Route::get('/{id}/edit', [OrdersController::class, 'editSO'])->name('edit');
        Route::put('/{id}/update', [OrdersController::class, 'updateSO'])->name('update');

        // Route Cek Relasi & Hapus SO
        Route::get('/{id}/check-relations', [OrdersController::class, 'checkRelationsSO'])->name('check-relations');
        Route::delete('/{id}', [OrdersController::class, 'destroySO'])->name('destroy');
    });

Route::get(
    '/penjualan/sales-order/{id}/detail',
    [OrdersController::class, 'detailSO']
)
    ->name('penjualan.sales-order.detail');
Route::get('/so/{id}', [OrdersController::class, 'showDetailSO']);
/*
|--------------------------------------------------------------------------
| DELIVERY NOTE PEMBELIAN
|--------------------------------------------------------------------------
*/

Route::prefix('pembelian/delivery-note')
    ->name('pembelian.delivery-note.')
    ->group(function () {
        Route::get('/', [DeliveryNoteController::class, 'indexMasuk'])->name('index');
        Route::get('/create', [DeliveryNoteController::class, 'createMasuk'])->name('create');
        Route::post('/', [DeliveryNoteController::class, 'store'])
            ->name('store')
            ->defaults('type', 'masuk');
        Route::get('/{deliveryNote}/check-relations', [DeliveryNoteController::class, 'checkRelations'])->name('check-relations');
        Route::get('/{deliveryNote}/edit', [DeliveryNoteController::class, 'edit'])->name('edit');
        Route::put('/{deliveryNote}', [DeliveryNoteController::class, 'update'])->name('update');
        Route::delete('/{deliveryNote}', [DeliveryNoteController::class, 'destroy'])->name('destroy');
        Route::get('/{deliveryNote}', [DeliveryNoteController::class, 'show'])->name('show');
    });
Route::get('/dnpo/{id}', [DeliveryNoteController::class, 'showDetailPO']);
Route::get('/order/{id}/details', [DeliveryNoteController::class, 'getOrderDetails']);



/*
|--------------------------------------------------------------------------
| DELIVERY NOTE PENJUALAN
|--------------------------------------------------------------------------
*/

Route::prefix('penjualan/delivery-note')
    ->name('penjualan.delivery-note.')
    ->group(function () {
        Route::get('/', [DeliveryNoteController::class, 'indexKeluar'])->name('index');
        Route::get('/create', [DeliveryNoteController::class, 'createKeluar'])->name('create');
        Route::post('/', [DeliveryNoteController::class, 'store'])
            ->name('store')
            ->defaults('type', 'keluar');

        // Route API untuk cek relasi Surat Jalan sebelum delete
        Route::get('/{deliveryNote}/check-relations', [DeliveryNoteController::class, 'checkRelations'])->name('check-relations');

        Route::get('/{deliveryNote}/edit', [DeliveryNoteController::class, 'editKeluar'])->name('edit');
        Route::put('/{deliveryNote}', [DeliveryNoteController::class, 'updateKeluar'])->name('update');
        Route::delete('/{deliveryNote}', [DeliveryNoteController::class, 'destroy'])->name('destroy');
        Route::get('/{deliveryNote}', [DeliveryNoteController::class, 'show'])->name('show');
    });
Route::get('/dnso/{id}', [DeliveryNoteController::class, 'showDetailSO']);
Route::get('/dnso/{id}/print', [DeliveryNoteController::class, 'print'])
    ->name('dnso.print');
Route::get('/dnso/{id}/printDot', [DeliveryNoteController::class, 'printDot'])
    ->name('dnso.printDot');

/*
|--------------------------------------------------------------------------
| INVOICE PEMBELIAN (MASUK)
|--------------------------------------------------------------------------
*/

Route::prefix('pembelian/invoice')
    ->name('pembelian.invoice.')
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'indexMasuk'])->name('index');
        Route::get('/create', [InvoiceController::class, 'createMasuk'])->name('create');
        Route::post('/', [InvoiceController::class, 'storeMasuk'])->name('store');
        Route::get('/{id}/edit', [InvoiceController::class, 'editMasuk'])->name('edit');
        Route::put('/{id}', [InvoiceController::class, 'updateMasuk'])->name('update');
    });

Route::get('/invPurchase/{id}/detail', [InvoiceController::class, 'detailPurchase']);
/*
|--------------------------------------------------------------------------
| INVOICE PENJUALAN (KELUAR)
|--------------------------------------------------------------------------
*/

Route::prefix('penjualan/invoice')
    ->name('penjualan.invoice.')
    ->group(function () {
        Route::get('/', [InvoiceController::class, 'indexKeluar'])->name('index');
        Route::get('/create', [InvoiceController::class, 'createKeluar'])->name('create');
        Route::post('/', [InvoiceController::class, 'storeKeluar'])->name('store');

        // TAMBAHIN DUA JALUR INI UNTUK UPDATE DATA KELUAR
        Route::get('/{id}/edit', [InvoiceController::class, 'editKeluar'])->name('edit');
        Route::put('/{id}', [InvoiceController::class, 'updateKeluar'])->name('update');
    });

Route::get('/invSales/{id}/detail', [InvoiceController::class, 'detailSales']);
Route::get('/invSales/{id}/print', [InvoiceController::class, 'print'])
    ->name('invSales.print');
Route::get('/invSales/{id}/printDot', [InvoiceController::class, 'printDot'])
    ->name('invSales.printDot');
Route::get('/invoice/{id}/print-ongkir', [InvoiceController::class, 'printOngkir'])
    ->name('invSales.printOngkir');

Route::get('/penjualan/data/export', [InvoiceController::class, 'exportPenjualan'])->name('penjualan.data.export');
Route::get('/penjualan/data/print', [InvoiceController::class, 'printPenjualan'])->name('penjualan.data.print');
/*
|--------------------------------------------------------------------------
| DATA PEMBELIAN
|--------------------------------------------------------------------------
*/
Route::get('/pembelian/data-pembelian', [InvoiceController::class, 'dataPembelian'])->name('pembelian.data-pembelian.index');
Route::get('/pembelian/data-pembelian/export', [InvoiceController::class, 'exportPembelian'])->name('pembelian.data-pembelian.export');
Route::get('/pembelian/data-pembelian/pdf', [InvoiceController::class, 'exportPembelianpdf'])->name('pembelian.data-pembelian.pdf');

Route::get('/pembelian/data-pembelian/print', [InvoiceController::class, 'printPembelian'])->name('pembelian.data-pembelian.print');

Route::get('/pembelian/data-pembelian', [InvoiceController::class, 'dataPembelian'])->name('pembelian.data-pembelian.index');
Route::get('/pembelian/data-pembelian/print', [InvoiceController::class, 'printPembelian'])->name('pembelian.data-pembelian.print');
Route::get('/api/invoice/{id}/payments', [InvoiceController::class, 'getPayments']);

Route::get('/pembelian/invoice/{id}/check-relations', [InvoiceController::class, 'checkRelationMasuk'])->name('pembelian.invoice.check-relations');

// Route Delete Invoice Pembelian
Route::delete('/pembelian/invoice/{id}', [InvoiceController::class, 'destroyKeluar'])->name('pembelian.invoice.destroy');


/*
|--------------------------------------------------------------------------
| DATA PENJUALAN
|--------------------------------------------------------------------------
*/
Route::get('/penjualan/data-penjualan', [InvoiceController::class, 'dataPenjualan'])->name('penjualan.data-penjualan.index');
Route::get('/penjualan/data-penjualan/export', [InvoiceController::class, 'exportPenjualan'])->name('penjualan.data-penjualan.export');
Route::get('/penjualan/data-penjualan/pdf', [InvoiceController::class, 'exportPenjualanpdf'])->name('penjualan.data-penjualan.pdf');
Route::get('/penjualan/data-penjualan/print', [InvoiceController::class, 'printPenjualan'])->name('penjualan.data-penjualan.print');

Route::get('/penjualan/data-penjualan/print', [InvoiceController::class, 'printPenjualan'])->name('penjualan.data-penjualan.print');
Route::get('/api/piutang/{id}/payments', [InvoiceController::class, 'getPaymentsPiutang']);

Route::get('/penjualan/invoice/{id}/check-relations', [InvoiceController::class, 'checkRelationKeluar'])->name('penjualan.invoice.check-relations');

// Route Delete Invoice Penjualan
Route::delete('/penjualan/invoice/{id}', [InvoiceController::class, 'destroyKeluar'])->name('penjualan.invoice.destroy');

//payment penjualan
Route::get('/penjualan/payment', [PaymentController::class, 'index'])
    ->name('penjualan.payment.index');

Route::get('/penjualan/payment/{payment}', [PaymentController::class, 'show'])
    ->name('penjualan.payment.show');


/*
|--------------------------------------------------------------------------
| HUTANG
|--------------------------------------------------------------------------
*/
Route::get('/pembelian/hutang', [InvoiceController::class, 'laporanHutang'])->name('pembelian.hutang.index');
Route::get('/api/hutang/{supplierId}', [InvoiceController::class, 'getHutangDetail']);
Route::post('/pembelian/hutang/bayar', [InvoiceController::class, 'bayarHutang'])->name('pembelian.hutang.bayar');

/*
|--------------------------------------------------------------------------
| HUTANG
|--------------------------------------------------------------------------
*/
Route::get('/penjualan/piutang', [InvoiceController::class, 'laporanPiutang'])->name('penjualan.piutang.index');
Route::get('/api/piutang/{customerId}', [InvoiceController::class, 'getPiutangDetail']);
Route::post('/penjualan/piutang/bayar', [InvoiceController::class, 'bayarPiutang'])->name('penjualan.piutang.bayar');

/*
|--------------------------------------------------------------------------
| ABSESNSI
|--------------------------------------------------------------------------
*/
Route::get('/absensi/print', [AbsensiController::class, 'print'])->name('absensi.print');

Route::prefix('absensi')->name('absensi.')->group(function () {

    // Route untuk Absen Karyawan
    Route::get('absen-karyawan', [AbsensiController::class, 'index'])->name('absen-karyawan.index');
    Route::post('absen-karyawan', [AbsensiController::class, 'store'])->name('absen-karyawan.store');

    //Route untuk print
    // INI YANG KURANG: Route untuk Handle AJAX Detail
    Route::get('absen-karyawan/detail/{id}', [AbsensiController::class, 'getDetail'])->name('absen-karyawan.detail');

    // Route untuk Premi Hadir
    Route::get('premi-hadir', [AbsensiController::class, 'premiIndex'])->name('premi-hadir.index');



    // Route Resource lainnya
    Route::resource('premi-karyawan', PremiUserController::class);
    Route::resource('sewa-kendaraan', SewaKendaraanController::class);

    Route::get(
        'premi-hadir/detail/{id}',
        [AbsensiController::class, 'detailPremi']
    );
});

Route::get('/absensi/premi/{id}/print', [AbsensiController::class, 'printPremi'])->name('absensi.premi.print');
Route::get('/absensi/sewa/{id}/print', [AbsensiController::class, 'printSewa'])->name('absensi.sewa.print');


/*
|--------------------------------------------------------------------------
| KAS
|--------------------------------------------------------------------------
*/

Route::prefix('petty_cash')->name('petty_cash.')->group(function () {

    Route::get('kas', [KasController::class, 'index'])->name('kas.index');
    Route::get('kas/create', [KasController::class, 'create'])->name('kas.create');
    Route::post('kas', [KasController::class, 'store'])->name('kas.store');
    Route::get('kas/{id}/edit', [KasController::class, 'edit'])->name('kas.edit');
    Route::put('kas/{id}', [KasController::class, 'update'])->name('kas.update');
    // Route::get('/', [KasController::class, 'index'])->name('index');

    // Route::post('/store', [KasController::class, 'store'])->name('store');

    Route::get('voucher', [KasController::class, 'indexVoucher'])->name('voucher.index');
    Route::get('voucher/{id}/detail', [KasController::class, 'detailVoucher'])
        ->name('voucher.detail');
    Route::get('voucher/create', [KasController::class, 'createVoucher'])->name('voucher.create');
    Route::post('voucher', [KasController::class, 'storeVoucher'])->name('voucher.store');
    Route::delete('voucher/{id}', [KasController::class, 'deleteVoucher'])->name('voucher.destroy');
    Route::get('voucher/{id}/print', [KasController::class, 'printVoucher'])
        ->name('voucher.print');
});

Route::get('kas/get-by-date', [KasController::class, 'getByDate']);

/*
|--------------------------------------------------------------------------
| AJAX
|--------------------------------------------------------------------------
*/

Route::get(
    '/invoice/delivery-note/{id}',
    [InvoiceController::class, 'getDeliveryNoteDetail']
)
    ->name('invoice.getDeliveryNoteDetail');


// PENJUALAN
// Route::prefix('penjualan')->name('penjualan.')->group(function () {
//     Route::get('invoice', [InvoiceController::class, 'index'])
//         ->defaults('type', 'penjualan')
//         ->name('invoice.index');

//     Route::get('invoice/create', [InvoiceController::class, 'create'])
//         ->defaults('type', 'penjualan')
//         ->name('invoice.create');
// });

// // PEMBELIAN
// Route::prefix('pembelian')->name('pembelian.')->group(function () {
//     Route::get('invoice', [InvoiceController::class, 'index'])
//         ->defaults('type', 'pembelian')
//         ->name('invoice.index');

//     Route::get('invoice/create', [InvoiceController::class, 'create'])
//         ->defaults('type', 'pembelian')
//         ->name('invoice.create');

// });

// Route::prefix('pembelian')->name('pembelian.')->group(function () {
//     Route::resource('invoice', InvoiceController::class);
// });

//modal ROUTEE


/*
|--------------------------------------------------------------------------
|Harga
|--------------------------------------------------------------------------
*/
Route::prefix('barang/{barang}')->group(function () {
    Route::get('/harga', [BarangHargaController::class, 'index'])->name('barang.harga.index');
    Route::post('/harga', [BarangHargaController::class, 'store'])->name('barang.harga.store');
    Route::delete('/harga/{id}', [BarangHargaController::class, 'destroy'])->name('barang.harga.destroy');
});
Route::get('/get-harga-barang', [App\Http\Controllers\BarangHargaController::class, 'getHargaAjax'])->name('barang.get-harga');




Route::get(
    '/pembelian/delivery-note/{id}/details',
    [InvoiceController::class, 'getDeliveryNoteDetail']
);

Route::get(
    '/penjualan/delivery-note/{id}/details',
    [InvoiceController::class, 'getDeliveryNoteDetail']
);

/*
|--------------------------------------------------------------------------
|Inventory
|--------------------------------------------------------------------------
*/
Route::prefix('gudang/sampel')
    ->name('gudang.sampel.')
    ->group(function () {
        Route::get('/', [SampelController::class, 'index'])->name('index');
        Route::get('/create', [SampelController::class, 'create'])->name('create');
        Route::post('/store', [SampelController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SampelController::class, 'edit'])->name('edit');
        Route::put('/{id}/update', [SampelController::class, 'update'])->name('update');

        // Jalur AJAX untuk isi modal detail html
        Route::get('/{id}/html-detail', [SampelController::class, 'htmlDetail'])->name('html-detail');
    });
Route::get('/sampel/{id}/print', [SampelController::class, 'print'])
    ->name('sampel.print');

require __DIR__ . '/auth.php';
