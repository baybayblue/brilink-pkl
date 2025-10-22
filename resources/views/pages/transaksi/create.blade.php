@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Transaksi Baru</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Transaksi Baru</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Transaksi Baru #{{ $nextNoTransaksi }}</h5>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan!</h4>
                        <p>Ada beberapa masalah dengan data yang Anda masukkan. Silakan periksa kolom yang ditandai merah.</p>
                        <hr>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transaksi.store') }}" method="POST" id="transaksi-form">
                    @csrf
                    <input type="hidden" name="no_transaksi" value="{{ $nextNoTransaksi }}">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipe_transaksi">Tipe Transaksi</label>
                                <select name="tipe_transaksi" id="tipe_transaksi" class="form-control" required>
                                    <option value="jasa_produk" {{ old('tipe_transaksi', 'jasa_produk') == 'jasa_produk' ? 'selected' : '' }}>Jasa / Produk</option>
                                    <option value="brilink" {{ old('tipe_transaksi') == 'brilink' ? 'selected' : '' }}>BRILink</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="pelanggan_id" id="label_pelanggan">Nama Pemesan</label>
                                <select name="pelanggan_id" id="pelanggan_id" class="form-control @error('pelanggan_id') is-invalid @enderror">
                                    <option value="">Pilih Pelanggan (atau biarkan kosong)</option>
                                    @foreach ($pelanggan as $item)
                                        <option value="{{ $item->id }}" data-alamat="{{ $item->alamat }}" data-telp="{{ $item->no_hp }}" {{ old('pelanggan_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pelanggan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="info_pelanggan_detail">
                                <div class="form-group">
                                    <label for="alamat_pelanggan">Alamat</label>
                                    <input type="text" id="alamat_pelanggan" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="telp_pelanggan">Telp</label>
                                    <input type="text" id="telp_pelanggan" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_order">Tanggal Order</label>
                                <input type="date" name="tanggal_order" id="tanggal_order" class="form-control @error('tanggal_order') is-invalid @enderror" value="{{ old('tanggal_order', date('Y-m-d')) }}" required>
                                @error('tanggal_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="form-jasa-produk-sidebar">
                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai') }}">
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9">
                            <div id="form-jasa-produk">
                                <h6>Detail Produk</h6>
                                <div id="produk-items-container">
                                    @include('pages.transaksi.produk_item_row', ['index' => 0, 'produks' => $produks])
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add-produk-item">Tambah Baris Produk</button>
                                <hr>
                                <div class="form-group">
                                    <label for="total_keseluruhan">Total Keseluruhan</label>
                                    <input type="text" name="total_keseluruhan" id="total_keseluruhan" class="form-control @error('total_keseluruhan') is-invalid @enderror" value="{{ old('total_keseluruhan', 0) }}" readonly>
                                     @error('total_keseluruhan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="uang_muka">Uang Muka</label>
                                    <input type="number" name="uang_muka" id="uang_muka" class="form-control @error('uang_muka') is-invalid @enderror" value="{{ old('uang_muka', 0) }}" min="0" step="0.01">
                                    @error('uang_muka')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="diskon">Diskon</label>
                                    <input type="number" name="diskon" id="diskon" class="form-control @error('diskon') is-invalid @enderror" value="{{ old('diskon', 0) }}" min="0" step="0.01">
                                    @error('diskon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="sisa">Sisa Pembayaran</label>
                                    <input type="text" name="sisa" id="sisa" class="form-control" value="{{ old('sisa', 0) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="status_pengerjaan">Status Pengerjaan</label>
                                    <select name="status_pengerjaan" id="status_pengerjaan" class="form-control @error('status_pengerjaan') is-invalid @enderror" required>
                                        <option value="menunggu export" {{ old('status_pengerjaan') == 'menunggu export' ? 'selected' : '' }}>Menunggu Export</option>
                                        <option value="belum dikerjakan" {{ old('status_pengerjaan', 'belum dikerjakan') == 'belum dikerjakan' ? 'selected' : '' }}>Belum Dikerjakan</option>
                                        <option value="proses desain" {{ old('status_pengerjaan') == 'proses desain' ? 'selected' : '' }}>Proses Desain</option>
                                        <option value="proses produksi" {{ old('status_pengerjaan') == 'proses produksi' ? 'selected' : '' }}>Proses Produksi</option>
                                        <option value="selesai" {{ old('status_pengerjaan') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status_pengerjaan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div id="form-brilink" style="display: none;">
                                <h6>Detail Transaksi BRILink</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="jenis_transaksi_brilink">Jenis Transaksi</label>
                                            <select name="jenis_transaksi_brilink" id="jenis_transaksi_brilink" class="form-control @error('jenis_transaksi_brilink') is-invalid @enderror">
                                                <option value="tarik_tunai">Tarik Tunai</option>
                                                <option value="transfer">Transfer</option>
                                                <option value="setor_tunai">Setor Tunai</option>
                                                <option value="pembayaran_tagihan">Pembayaran Tagihan</option>
                                                <option value="lainnya">Lainnya</option>
                                            </select>
                                            @error('jenis_transaksi_brilink') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bank_tujuan">Bank Tujuan / Keterangan</label>
                                            <input type="text" name="bank_tujuan" id="bank_tujuan" class="form-control @error('bank_tujuan') is-invalid @enderror" 
                                                   placeholder="Ketik atau pilih dari daftar..." list="daftar_bank_tujuan" value="{{ old('bank_tujuan') }}">
                                            <datalist id="daftar_bank_tujuan">
                                                <option value="BRI"><option value="BCA"><option value="Mandiri"><option value="BNI"><option value="BSI"><option value="Tarik Tunai"><option value="Bayar Listrik"><option value="Bayar BPJS"><option value="Bayar PDAM"><option value="Isi Pulsa"><option value="Top Up ShopeePay"><option value="Top Up GoPay"><option value="Top Up OVO"><option value="Top Up DANA">
                                            </datalist>
                                            @error('bank_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="no_rekening_tujuan">No. Rekening / ID Pelanggan</label>
                                            <input type="text" name="no_rekening_tujuan" id="no_rekening_tujuan" class="form-control @error('no_rekening_tujuan') is-invalid @enderror" value="{{ old('no_rekening_tujuan') }}">
                                            @error('no_rekening_tujuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_pemilik_rekening">Atas Nama</label>
                                            <input type="text" name="nama_pemilik_rekening" id="nama_pemilik_rekening" class="form-control @error('nama_pemilik_rekening') is-invalid @enderror" value="{{ old('nama_pemilik_rekening') }}">
                                            @error('nama_pemilik_rekening') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <h6>Detail Pembayaran BRILink</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="nominal_brilink">Nominal Transaksi (Rp)</label>
                                            <input type="number" name="nominal_brilink" id="nominal_brilink" class="form-control @error('nominal_brilink') is-invalid @enderror" value="{{ old('nominal_brilink', 0) }}" min="0">
                                            @error('nominal_brilink') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="biaya_admin_brilink">Biaya Admin (Rp)</label>
                                            <input type="number" name="biaya_admin_brilink" id="biaya_admin_brilink" class="form-control @error('biaya_admin_brilink') is-invalid @enderror" value="{{ old('biaya_admin_brilink', 0) }}" min="0">
                                            @error('biaya_admin_brilink') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="total_brilink">Total Dibayar Pelanggan</label>
                                            <input type="text" id="total_brilink" class="form-control" readonly style="background-color: #e9ecef;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
                            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script>
    let produkItemIndex = {{ old('nama_produk') ? count(old('nama_produk')) : 1 }};

    document.addEventListener('DOMContentLoaded', function() {
        const tipeTransaksiSelect = document.getElementById('tipe_transaksi');
        
        const formJasaProduk = document.getElementById('form-jasa-produk');
        const formJasaProdukSidebar = document.getElementById('form-jasa-produk-sidebar');
        
        const formBrilink = document.getElementById('form-brilink');
        const jenisTransaksiBrilink = document.getElementById('jenis_transaksi_brilink');
        const nominalBrilink = document.getElementById('nominal_brilink');
        const adminBrilink = document.getElementById('biaya_admin_brilink');
        const totalBrilink = document.getElementById('total_brilink');

        const labelPelanggan = document.getElementById('label_pelanggan');
        const infoPelangganDetail = document.getElementById('info_pelanggan_detail');

        // --- FUNGSI UTAMA YANG DIPERBAIKI ---
        function toggleFormFields() {
            const isJasa = tipeTransaksiSelect.value === 'jasa_produk';

            // Mengatur tampilan (display)
            formJasaProduk.style.display = isJasa ? 'block' : 'none';
            formJasaProdukSidebar.style.display = isJasa ? 'block' : 'none';
            infoPelangganDetail.style.display = isJasa ? 'block' : 'none';
            formBrilink.style.display = isJasa ? 'none' : 'block';
            
            labelPelanggan.textContent = isJasa ? 'Nama Pemesan' : 'Nama Pelanggan (Opsional)';

            // Menonaktifkan (disable) input yang tidak terlihat
            const jasaInputs = formJasaProduk.querySelectorAll('input, select, textarea');
            jasaInputs.forEach(input => input.disabled = !isJasa);

            const jasaSidebarInputs = formJasaProdukSidebar.querySelectorAll('input, select, textarea');
            jasaSidebarInputs.forEach(input => input.disabled = !isJasa);

            const brilinkInputs = formBrilink.querySelectorAll('input, select, textarea');
            brilinkInputs.forEach(input => input.disabled = isJasa);
        }
        
        function calculateBrilinkTotal() {
            const nominal = parseFloat(nominalBrilink.value) || 0;
            const admin = parseFloat(adminBrilink.value) || 0;
            const total = nominal + admin;
            totalBrilink.value = formatRupiah(total);
        }

        function calculateAdminFee() {
            const jenis = jenisTransaksiBrilink.value;
            const nominal = parseFloat(nominalBrilink.value) || 0;
            let adminFee = 0;

            switch (jenis) {
                case 'transfer':
                    adminFee = 6000;
                    break;
                case 'tarik_tunai':
                    if (nominal <= 500000) {
                        adminFee = 5000;
                    } else if (nominal <= 1000000) {
                        adminFee = 7000;
                    } else if (nominal <= 2500000) {
                        adminFee = 10000;
                    } else {
                        adminFee = 15000; 
                    }
                    break;
                case 'setor_tunai':
                     if (nominal <= 5000000) {
                        adminFee = 5000;
                    } else {
                        adminFee = 10000;
                    }
                    break;
                case 'pembayaran_tagihan':
                    adminFee = 3000;
                    break;
                case 'lainnya':
                    adminFee = 0;
                    break;
            }
            
            adminBrilink.value = adminFee;
            calculateBrilinkTotal();
        }

        tipeTransaksiSelect.addEventListener('change', toggleFormFields);
        
        if (nominalBrilink) {
            nominalBrilink.addEventListener('input', calculateAdminFee);
        }
        if (jenisTransaksiBrilink) {
            jenisTransaksiBrilink.addEventListener('change', calculateAdminFee);
        }
        
        if (adminBrilink) {
            adminBrilink.addEventListener('input', calculateBrilinkTotal);
        }

        // Panggil fungsi ini saat halaman dimuat pertama kali
        toggleFormFields();
        calculateAdminFee();
        
        const pelangganSelect = document.getElementById('pelanggan_id');
        const alamatPelangganInput = document.getElementById('alamat_pelanggan');
        const telpPelangganInput = document.getElementById('telp_pelanggan');

        function updatePelangganInfo() {
            const selectedOption = pelangganSelect.options[pelangganSelect.selectedIndex];
            if (selectedOption && selectedOption.value) { 
                alamatPelangganInput.value = selectedOption.dataset.alamat || '';
                telpPelangganInput.value = selectedOption.dataset.telp || '';
            } else {
                alamatPelangganInput.value = '';
                telpPelangganInput.value = '';
            }
        }

        pelangganSelect.addEventListener('change', updatePelangganInfo);
        updatePelangganInfo();

        function formatRupiah(angka) {
            if (angka === null || angka === undefined || isNaN(angka)) {
                return 'Rp 0';
            }
            var reverse = angka.toString().split('').reverse().join(''),
                ribuan = reverse.match(/\d{1,3}/g);
            ribuan = ribuan.join('.').split('').reverse().join('');
            return 'Rp ' + ribuan;
        }

        function calculateGrandTotalAndRemaining() {
            let grandTotal = 0;
            document.querySelectorAll('.item-total').forEach(function(element) {
                let value = element.value.replace(/Rp\s?|(\.)/g, '');
                grandTotal += parseFloat(value.replace(",", ".")) || 0;
            });

            document.getElementById('total_keseluruhan').value = formatRupiah(grandTotal);

            const uangMuka = parseFloat(document.getElementById('uang_muka').value) || 0;
            const diskon = parseFloat(document.getElementById('diskon').value) || 0;

            let sisa = grandTotal - uangMuka - diskon;
            if (sisa < 0) sisa = 0; 

            document.getElementById('sisa').value = formatRupiah(sisa);
        }

        document.getElementById('uang_muka').addEventListener('input', calculateGrandTotalAndRemaining);
        document.getElementById('diskon').addEventListener('input', calculateGrandTotalAndRemaining);


        document.getElementById('add-produk-item').addEventListener('click', function() {
            const container = document.getElementById('produk-items-container');
            fetch('/transaksi/get-produk-item-row?index=' + produkItemIndex)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(text) });
                    }
                    return response.text();
                })
                .then(html => {
                    container.insertAdjacentHTML('beforeend', html);
                    initializeProdukRow(produkItemIndex);
                    produkItemIndex++;
                    calculateGrandTotalAndRemaining();
                })
                .catch(error => {
                    console.error('Error adding product row:', error);
                    alert('Gagal menambahkan baris produk. Silakan cek konsol browser untuk detail error.');
                });
        });

        document.getElementById('produk-items-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-produk-item') || e.target.closest('.remove-produk-item')) {
                const row = e.target.closest('.produk-item');
                if (row) {
                    row.remove();
                    calculateGrandTotalAndRemaining();
                }
            }
        });

        function initializeProdukRow(index) {
            const row = document.querySelector(`.produk-item[data-index="${index}"]`);
            if (!row) return;

            const produkSelect = row.querySelector('.produk-name');
            const produkIdInput = row.querySelector('.produk-id');
            const produkUkuranInput = row.querySelector('.produk-ukuran');
            const produkSatuanInput = row.querySelector('.produk-satuan');
            const itemQtyInput = row.querySelector('.item-qty');
            const itemPriceInput = row.querySelector('.item-price');
            const itemTotalInput = row.querySelector('.item-total');

            produkSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) { 
                    produkIdInput.value = selectedOption.dataset.id || '';
                    produkUkuranInput.value = selectedOption.dataset.ukuran || '';
                    produkSatuanInput.value = selectedOption.dataset.satuan || '';
                    itemPriceInput.value = parseFloat(selectedOption.dataset.harga) || 0;
                } else {
                    produkIdInput.value = '';
                    produkUkuranInput.value = ''; 
                    produkSatuanInput.value = '';
                    itemPriceInput.value = 0;
                }
                calculateItemTotal(row);
            });

            itemQtyInput.addEventListener('input', function() { calculateItemTotal(row); });
            itemPriceInput.addEventListener('input', function() { calculateItemTotal(row); });

            function calculateItemTotal(rowElement) {
                const qty = parseFloat(rowElement.querySelector('.item-qty').value) || 0;
                const price = parseFloat(rowElement.querySelector('.item-price').value) || 0;
                const itemTotal = qty * price;
                rowElement.querySelector('.item-total').dataset.value = itemTotal;
                rowElement.querySelector('.item-total').value = formatRupiah(itemTotal);
                calculateGrandTotalAndRemaining();
            }

            calculateItemTotal(row);

            if (produkSelect.value) {
                produkSelect.dispatchEvent(new Event('change'));
            }
        }

        document.querySelectorAll('.produk-item').forEach(function(row) {
            const index = row.dataset.index;
            initializeProdukRow(index);
        });

        calculateGrandTotalAndRemaining();
    });
</script>
@endpush

