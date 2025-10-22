@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-edit mr-2"></i>Edit Transaksi</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('transaksi.index') }}">Data Transaksi</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Edit Transaksi #{{ $transaksi->no_transaksi }}</h5>
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
                
                {{-- ================================================= --}}
                {{--       KONDISI UNTUK MEMBEDAKAN TAMPILAN          --}}
                {{-- ================================================= --}}

                @if($transaksi->tipe_transaksi == 'brilink')
                    {{-- TAMPILAN UNTUK BRILINK (READ-ONLY) --}}
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi</h5>
                        <p class="mb-0">Transaksi BRILink tidak dapat diubah untuk menjaga integritas data. Jika terjadi kesalahan, hapus transaksi ini dan buat yang baru.</p>
                    </div>

                    <dl class="row">
                        <dt class="col-sm-3">No. Transaksi</dt>
                        <dd class="col-sm-9">{{ $transaksi->no_transaksi }}</dd>

                        <dt class="col-sm-3">Tanggal</dt>
                        <dd class="col-sm-9">{{ $transaksi->tanggal_order->format('d M Y') }}</dd>

                        <dt class="col-sm-3">Pelanggan</dt>
                        <dd class="col-sm-9">{{ $transaksi->pelanggan->nama ?? 'Umum' }}</dd>

                        <dt class="col-sm-3">Jenis Transaksi</dt>
                        <dd class="col-sm-9 text-capitalize">{{ str_replace('_', ' ', $transaksi->detail_brilink['jenis'] ?? '-') }}</dd>

                        <dt class="col-sm-3">Tujuan / Keterangan</dt>
                        <dd class="col-sm-9">{{ $transaksi->detail_brilink['bank_tujuan'] ?? '-' }}</dd>

                        <dt class="col-sm-3">No. Rekening / ID</dt>
                        <dd class="col-sm-9">{{ $transaksi->detail_brilink['no_rekening'] ?? '-' }}</dd>

                        <dt class="col-sm-3">Atas Nama</dt>
                        <dd class="col-sm-9">{{ $transaksi->detail_brilink['atas_nama'] ?? '-' }}</dd>
                        
                        <dt class="col-sm-3">Nominal</dt>
                        <dd class="col-sm-9">Rp{{ number_format($transaksi->detail_brilink['nominal'] ?? 0, 0, ',', '.') }}</dd>

                        <dt class="col-sm-3">Biaya Admin</dt>
                        <dd class="col-sm-9">Rp{{ number_format($transaksi->detail_brilink['admin'] ?? 0, 0, ',', '.') }}</dd>

                        <dt class="col-sm-3 border-top pt-2">Total Dibayar</dt>
                        <dd class="col-sm-9 border-top pt-2"><strong>Rp{{ number_format($transaksi->total, 0, ',', '.') }}</strong></dd>
                    </dl>
                    
                    <div class="text-right mt-4">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>

                @else
                    {{-- TAMPILAN UNTUK JASA / PRODUK (FORM EDIT) --}}
                    <form action="{{ route('transaksi.update', $transaksi->id) }}" method="POST" id="transaksi-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="no_transaksi" value="{{ $transaksi->no_transaksi }}">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="pelanggan_id">Nama Pemesan</label>
                                    <select name="pelanggan_id" id="pelanggan_id" class="form-control @error('pelanggan_id') is-invalid @enderror">
                                        <option value="">Pilih Pelanggan</option>
                                        @foreach ($pelanggan as $item)
                                            <option value="{{ $item->id }}"
                                                    data-alamat="{{ $item->alamat }}"
                                                    data-telp="{{ $item->no_hp }}"
                                                    {{ old('pelanggan_id', $transaksi->pelanggan_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pelanggan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="alamat_pelanggan">Alamat</label>
                                    <input type="text" id="alamat_pelanggan" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="telp_pelanggan">Telp</label>
                                    <input type="text" id="telp_pelanggan" class="form-control" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_order">Tanggal Order</label>
                                    <input type="date" name="tanggal_order" id="tanggal_order" class="form-control @error('tanggal_order') is-invalid @enderror" value="{{ old('tanggal_order', $transaksi->tanggal_order->format('Y-m-d')) }}" required>
                                    @error('tanggal_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="tanggal_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $transaksi->tanggal_selesai ? $transaksi->tanggal_selesai->format('Y-m-d') : '') }}">
                                    @error('tanggal_selesai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-9">
                                <h6>Detail Produk</h6>
                                <div id="produk-items-container">
                                    @forelse ($transaksi->transaksiDetails as $index => $detail)
                                        @include('pages.transaksi.produk_item_row', [
                                            'index' => $index,
                                            'produks' => $produks,
                                            'detail' => $detail
                                        ])
                                    @empty
                                        @include('pages.transaksi.produk_item_row', ['index' => 0, 'produks' => $produks])
                                    @endforelse
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add-produk-item">Tambah Baris Produk</button>
                                <hr>
                                <div class="form-group">
                                    <label for="total_keseluruhan">Total Keseluruhan</label>
                                    <input type="text" name="total_keseluruhan" id="total_keseluruhan" class="form-control" value="{{ old('total_keseluruhan', $transaksi->total) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="uang_muka">Uang Muka</label>
                                    <input type="number" name="uang_muka" id="uang_muka" class="form-control @error('uang_muka') is-invalid @enderror" value="{{ old('uang_muka', $transaksi->uang_muka) }}" min="0" step="0.01">
                                    @error('uang_muka')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="diskon">Diskon</label>
                                    <input type="number" name="diskon" id="diskon" class="form-control @error('diskon') is-invalid @enderror" value="{{ old('diskon', $transaksi->diskon) }}" min="0" step="0.01">
                                    @error('diskon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label for="sisa">Sisa Pembayaran</label>
                                    <input type="text" name="sisa" id="sisa" class="form-control" value="{{ old('sisa', $transaksi->sisa) }}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="status_pengerjaan">Status Pengerjaan</label>
                                    <select name="status_pengerjaan" id="status_pengerjaan" class="form-control @error('status_pengerjaan') is-invalid @enderror" required>
                                        <option value="menunggu export" {{ old('status_pengerjaan', $transaksi->status_pengerjaan) == 'menunggu export' ? 'selected' : '' }}>Menunggu Export</option>
                                        <option value="belum dikerjakan" {{ old('status_pengerjaan', $transaksi->status_pengerjaan) == 'belum dikerjakan' ? 'selected' : '' }}>Belum Dikerjakan</option>
                                        <option value="proses desain" {{ old('status_pengerjaan', $transaksi->status_pengerjaan) == 'proses desain' ? 'selected' : '' }}>Proses Desain</option>
                                        <option value="proses produksi" {{ old('status_pengerjaan', $transaksi->status_pengerjaan) == 'proses produksi' ? 'selected' : '' }}>Proses Produksi</option>
                                        <option value="selesai" {{ old('status_pengerjaan', $transaksi->status_pengerjaan) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                    @error('status_pengerjaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">Update Transaksi</button>
                                <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Script ini hanya akan berjalan jika form edit ditampilkan (bukan untuk BRILink) --}}
@if($transaksi->tipe_transaksi != 'brilink')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    let produkItemIndex = {{ old('nama_produk') ? count(old('nama_produk')) : ($transaksi->transaksiDetails->count() > 0 ? $transaksi->transaksiDetails->count() : 1) }};

    document.addEventListener('DOMContentLoaded', function() {
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
                .then(response => response.text())
                .then(html => {
                    container.insertAdjacentHTML('beforeend', html);
                    initializeProdukRow(produkItemIndex);
                    produkItemIndex++;
                    calculateGrandTotalAndRemaining();
                })
                .catch(error => console.error('Error adding product row:', error));
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
            const itemQtyInput = row.querySelector('.item-qty');
            const itemPriceInput = row.querySelector('.item-price');

            produkSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const rowElement = this.closest('.produk-item');
                if (selectedOption && selectedOption.value) {
                    rowElement.querySelector('.produk-id').value = selectedOption.dataset.id || '';
                    rowElement.querySelector('.produk-ukuran').value = selectedOption.dataset.ukuran || '';
                    rowElement.querySelector('.produk-satuan').value = selectedOption.dataset.satuan || '';
                    rowElement.querySelector('.item-price').value = parseFloat(selectedOption.dataset.harga) || 0;
                } else {
                    rowElement.querySelector('.item-price').value = 0;
                }
                calculateItemTotal(rowElement);
            });

            itemQtyInput.addEventListener('input', () => calculateItemTotal(row));
            itemPriceInput.addEventListener('input', () => calculateItemTotal(row));

            function calculateItemTotal(rowElement) {
                const qty = parseFloat(rowElement.querySelector('.item-qty').value) || 0;
                const price = parseFloat(rowElement.querySelector('.item-price').value) || 0;
                const itemTotal = qty * price;
                rowElement.querySelector('.item-total').value = formatRupiah(itemTotal);
                calculateGrandTotalAndRemaining();
            }
        }

        document.querySelectorAll('.produk-item').forEach(function(row) {
            const index = row.dataset.index;
            initializeProdukRow(index);
        });

        calculateGrandTotalAndRemaining();
    });
</script>
@endif
@endpush
