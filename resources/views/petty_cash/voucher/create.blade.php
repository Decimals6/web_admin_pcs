@extends('layouts.admin')

@section('content')
    <div class="container">
        <h4>Buat Voucher Reimburse</h4>

        <form action="{{ route('petty_cash.voucher.store') }}" method="POST">
            @csrf

            {{-- HEADER --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">

                        <div class="col-md-3">
                            <label>No Voucher</label>
                            <input type="text" name="no" class="form-control"
                                value="{{ generateVoucherNumber('vouchers', 'VPCS') }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control" required>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" onclick="loadKas()">
                                Ambil Data Kas
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="card">
                <div class="card-body">
                    <h5>Daftar Kas</h5>

                    <table class="table table-bordered" id="kasTable">
                        <thead>
                            <tr>
                                <th>Pilih</th>
                                <th>Tanggal</th>
                                <th>No Transaksi</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <button class="btn btn-success">Simpan Voucher</button>
                    <a href="{{ route('petty_cash.voucher.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        function loadKas() {
            let tglMulai = document.getElementById('tgl_mulai').value;
            let tglAkhir = document.getElementById('tgl_akhir').value;

            if (!tglMulai || !tglAkhir) {
                alert('Isi tanggal dulu!');
                return;
            }

            fetch(`/kas/get-by-date?tgl_mulai=${tglMulai}&tgl_akhir=${tglAkhir}`)
                .then(res => res.json())
                .then(data => {

                    let tbody = document.querySelector('#kasTable tbody');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="text-center">Tidak ada data</td></tr>`;
                        return;
                    }

                    data.forEach((row, index) => {
                        tbody.innerHTML += `
                        <tr>
                            <td>
                                <input type="checkbox" name="kas_ids[]" value="${row.id}" checked>
                            </td>
                            <td>${row.tanggal}</td>
                            <td>${row.no_transaksi}</td>
                            <td>${row.keterangan}</td>
                            <td class="text-end">${parseInt(row.debit).toLocaleString()}</td>
                            <td class="text-end">${parseInt(row.kredit).toLocaleString()}</td>
                        </tr>
                    `;
                    });

                })
                .catch(err => console.log(err));
        }
    </script>

@endsection