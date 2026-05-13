<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin PCS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- logo tab browser --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-danger btn-sm">Logout</button>
                    </form>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">Admin PCS</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Master Data -->
                        <li class="nav-item has-treeview
                    {{ request()->is('users*') || request()->is('barang*') ? 'menu-open' : '' }}">

                            <a href="#" class="nav-link
                        {{ request()->is('users*') || request()->is('barang*') ? 'active' : '' }}">
                                {{-- <i class="nav-icon fas fa-database"></i> --}}
                                <p>
                                    Master Data
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('barang.index') }}"
                                        class="nav-link {{ request()->is('barang*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-box"></i>
                                        <p>Barang</p>
                                    </a>
                                </li>

                                {{-- @if(auth()->user()->isAdmin()) --}}
                                <li class="nav-item">
                                    <a href="{{ route('users.index') }}"
                                        class="nav-link {{ request()->is('users*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>User</p>
                                    </a>
                                </li>
                                {{-- @endif --}}

                                <li class="nav-item">
                                    <a href="{{ route('suppliers.index') }}"
                                        class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-truck"></i>
                                        <p>Supplier</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('customers.index') }}"
                                        class="nav-link {{ request()->is('customers*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-users"></i>
                                        <p>Customer</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('banks.index') }}"
                                        class="nav-link {{ request()->is('banks*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-university"></i>
                                        <p>Bank</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Inventory --}}
                        <li class="nav-item has-treeview
                    {{ request()->is('users*') || request()->is('barang*') ? 'menu-open' : '' }}">

                            <a href="#" class="nav-link
                        {{ request()->is('users*') || request()->is('barang*') ? 'active' : '' }}">
                                <p>
                                    Inventory
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    {{-- <a href="{{ route('incoming-barangs.index') }}"
                                        class="nav-link {{ request()->is('incoming-barangs*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-download"></i>
                                        <p>Incoming Barang</p>
                                    </a> --}}
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('mutasi-barangs.index') }}"
                                        class="nav-link {{ request()->is('mutasi-barangs*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-exchange-alt"></i>
                                        <p>Mutasi Barang</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Register Surat Jalan --}}
                        {{-- <li class="nav-item {{ request()->is('penjualan/surat-jalan*') ? 'menu-open' : '' }}">
                            <a href="{{ route('surat-jalan.index') }}"
                                class="nav-link {{ request()->is('penjualan/surat-jalan*') ? 'active' : '' }}">
                                <p>Register Surat Jalan</p>
                            </a>
                        </li>
                        <li class="nav-item {{ request()->is('penjualan/faktur*') ? 'menu-open' : '' }}">
                            <a href="{{ route('faktur.index') }}"
                                class="nav-link {{ request()->is('penjualan/faktur*') ? 'active' : '' }}">
                                <p>Faktur Penjualan</p>
                            </a>
                        </li> --}}

                        <!-- Pembelian -->
                        <li class="nav-item has-treeview {{ request()->is('pembelian*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('pembelian*') ? 'active' : '' }}">
                                <p> Pembelian <i class="right fas fa-angle-left"></i> </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('pembelian.purchase-order.index') }}"
                                        class="nav-link {{ request()->is('pembelian/purchase-order*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file"></i>
                                        <p>Register PO Supplier</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('pembelian.delivery-note.index') }}"
                                        class="nav-link {{ request()->is('pembelian/delivery-note*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file"></i>
                                        <p>Surat Jalan Pembelian</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('pembelian.invoice.index') }}"
                                        class="nav-link {{ request()->is('pembelian/invoice*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file-invoice-dollar"></i>
                                        <p>Faktur Pembelian</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('pembelian.data-pembelian.index') }}"
                                        class="nav-link {{ request()->is('pembelian/data-pembelian*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-shopping-cart"></i>
                                        <p>Data Pembelian</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('pembelian.hutang.index') }}"
                                        class="nav-link {{ request()->is('pembelian/hutang*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-credit-card"></i>
                                        <p>Hutang Supplier</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <!-- Penjualan -->
                        <li class="nav-item has-treeview {{ request()->is('penjualan*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('penjualan*') ? 'active' : '' }}">
                                <p> Penjualan <i class="right fas fa-angle-left"></i> </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('penjualan.sales-order.index') }}"
                                        class="nav-link {{ request()->is('penjualan/sales-order*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file"></i>
                                        <p>Register SO Customer</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('penjualan.delivery-note.index') }}"
                                        class="nav-link {{ request()->is('penjualan/delivery-note*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file"></i>
                                        <p>Surat Jalan Penjualan</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('penjualan.invoice.index') }}"
                                        class="nav-link {{ request()->is('penjualan/invoice*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-solid fa-file-invoice-dollar"></i>
                                        <p>Faktur Penjualan</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('penjualan.data-penjualan.index') }}"
                                        class="nav-link {{ request()->is('penjualan/data-penjualan*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-cash-register"></i>
                                        <p>Data Penjualan</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('penjualan.piutang.index') }}"
                                        class="nav-link {{ request()->is('penjualan/piutang*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                                        <p>Piutang Customer</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <li class="nav-item has-treeview {{ request()->is('absensi*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('absensi*') ? 'active' : '' }}">

                                <p> Manajemen Absensi <i class="right fas fa-angle-left"></i> </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('absensi.absen-karyawan.index') }}"
                                        class="nav-link {{ request()->is('absensi/absen-karyawan*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-calendar-check"></i>
                                        <p>Absen Karyawan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.premi-hadir.index') }}"
                                        class="nav-link {{ request()->is('absensi/premi-hadir*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-hand-holding-usd"></i>
                                        <p>Premi Hadir</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.premi-karyawan.index') }}"
                                        class="nav-link {{ request()->is('absensi/premi-karyawan*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-coins"></i>
                                        <p>Premi Karyawan</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.sewa-kendaraan.index') }}"
                                        class="nav-link {{ request()->is('absensi/sewa-kendaraan*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-car"></i>
                                        <p>Sewa Kendaraan</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview {{ request()->is('petty_cash*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('petty_cash*') ? 'active' : '' }}">

                                <p> PettyCash <i class="right fas fa-angle-left"></i> </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('petty_cash.kas.index') }}"
                                        class="nav-link {{ request()->is('petty_cash/kas*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-money-bill-wave"></i>
                                        <p>Kas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('petty_cash.voucher.index') }}"
                                        class="nav-link {{ request()->is('petty_cash/voucher*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-receipt"></i>
                                        <p>Voucher</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Kas -->
                        {{-- <li class="nav-item {{ request()->is('kas*') ? 'menu-open' : '' }}">
                            <a href="{{ route('kas.index') }}"
                                class="nav-link {{ request()->is('kas*') ? 'active' : '' }}">
                                <p>Kas</p>
                            </a>
                        </li> --}}
                        {{-- <li class="nav-item {{ request()->is('absensi*') ? 'menu-open' : '' }}">
                            <a href="{{ route('kas.index') }}"
                                class="nav-link {{ request()->is('kas*') ? 'active' : '' }}">
                                <p>Absensi Karyawan</p>
                            </a>
                        </li> --}}

                    </ul>
                </nav>
            </div>
        </aside>


        <div class="content-wrapper p-3">
            @yield('content')
        </div>

    </div>

    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    {{-- SELECT2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @yield('scripts')

</body>

</html>