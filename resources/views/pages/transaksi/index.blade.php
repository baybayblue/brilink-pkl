@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-exchange-alt mr-2"></i>Data Transaksi</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Transaksi</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-alt mr-2"></i>Daftar Transaksi
                </h3>
                <div class="card-tools">
                    <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Transaksi Baru
                    </a>
                    <a href="{{ route('transaksi.export-excel', request()->query()) }}" class="btn btn-success btn-sm ml-2">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('transaksi.index') }}" method="GET" class="mb-4">
                    <div class="form-row">
                        <div class="col-md-auto mb-2">
                            <label for="search_query">Cari (Pelanggan / No. Order)</label>
                            <input type="text" name="search_query" id="search_query" class="form-control" placeholder="Nama atau No. Order" value="{{ request('search_query') }}">
                        </div>
                        <div class="col-md-auto mb-2">
                            <label for="start_date">Dari Tanggal</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-auto mb-2">
                            <label for="end_date">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        
                        {{-- BARU: Filter berdasarkan Tipe Transaksi --}}
                        <div class="col-md-auto mb-2">
                            <label for="tipe_transaksi">Tipe Transaksi</label>
                            <select name="tipe_transaksi" id="tipe_transaksi" class="form-control">
                                <option value="">Semua Tipe</option>
                                <option value="jasa_produk" {{ request('tipe_transaksi') == 'jasa_produk' ? 'selected' : '' }}>Jasa / Produk</option>
                                <option value="brilink" {{ request('tipe_transaksi') == 'brilink' ? 'selected' : '' }}>BRILink</option>
                            </select>
                        </div>
                        
                        <div class="col-md-auto mb-2">
                            <label for="limit">Limit</label>
                            <select name="limit" id="limit" class="form-control" onchange="this.form.submit()">
                                @foreach ([10, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" {{ request('limit', 10) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Cari</button>
                            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary ml-2"><i class="fas fa-sync-alt"></i> Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>No. Order</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Sisa</th>
                                <th>Status Bayar</th>
                                <th>Status Kerja</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksi as $item)
                            <tr id="transaksi-row-{{ $item->id }}">
                                <td>{{ $loop->iteration + ($transaksi->currentPage() - 1) * $transaksi->perPage() }}</td>
                                <td>
                                    <strong>{{ $item->no_transaksi }}</strong>
                                    <small class="d-block text-muted">{{ $item->tanggal_order->format('d M Y') }}</small>
                                    
                                    @if($item->tipe_transaksi == 'brilink')
                                        <span class="badge badge-info mt-1"><i class="fas fa-university mr-1"></i> BRILink</span>
                                    @endif
                                </td>
                                <td>{{ $item->pelanggan->nama ?? 'Umum' }}</td>
                                <td>Rp{{ number_format($item->total, 0, ',', '.') }}</td>
                                <td><strong>Rp{{ number_format($item->sisa, 0, ',', '.') }}</strong></td>
                                <td>
                                    @if($item->status_pembayaran == 'lunas' || $item->sisa <= 0)
                                        <span class="badge badge-success">LUNAS</span>
                                    @else
                                        <span class="badge badge-warning">BELUM LUNAS</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = '';
                                        switch ($item->status_pengerjaan) {
                                            case 'menunggu export': $statusClass = 'badge-secondary'; break;
                                            case 'belum dikerjakan': $statusClass = 'badge-danger'; break;
                                            case 'proses desain': $statusClass = 'badge-info'; break;
                                            case 'proses produksi': $statusClass = 'badge-primary'; break;
                                            case 'selesai': $statusClass = 'badge-success'; break;
                                            default: $statusClass = 'badge-light';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status_pengerjaan)) }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($item->sisa > 0)
                                        <button type="button" class="btn btn-success btn-sm mb-1" onclick="showPelunasanModal('{{ $item->id }}', '{{ $item->sisa }}', '{{ $item->total }}', '{{ $item->diskon }}')">
                                            <i class="fas fa-money-bill-wave"></i> Bayar
                                        </button>
                                    @endif
                                    <div class="btn-group">
                                        @if($item->tipe_transaksi != 'brilink')
                                            <a href="{{ route('transaksi.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                        @endif
                                        
                                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('transaksi.print-receipt', $item->id) }}" target="_blank"><i class="fas fa-print fa-fw mr-2"></i>Cetak Struk</a>
                                            
                                            @if($item->tipe_transaksi != 'brilink')
                                                <a class="dropdown-item" href="{{ route('transaksi.print-invoice', $item->id) }}" target="_blank"><i class="fas fa-file-invoice fa-fw mr-2"></i>Cetak Invoice</a>
                                            @endif
                                            
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item text-danger" type="button" onclick="confirmDelete('{{ $item->id }}', '{{ $item->no_transaksi }}')">
                                                <i class="fas fa-trash-alt fa-fw mr-2"></i>Hapus Transaksi
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data transaksi ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($transaksi->isNotEmpty())
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="3" class="text-right">Total (Halaman Ini):</th>
                                <th>Rp{{ number_format($totalKeseluruhanTransaksi, 0, ',', '.') }}</th>
                                <th>Rp{{ number_format($totalPiutang, 0, ',', '.') }}</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $transaksi->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pelunasanModal" tabindex="-1" role="dialog" aria-labelledby="pelunasanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pelunasanModalLabel">Pelunasan Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPelunasan" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="transaksi_id" id="modal_transaksi_id">

                    <div class="form-group">
                        <label>Total Tagihan</label>
                        <input type="text" id="modal_total_harus_dibayar" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="modal_sisa_pembayaran">Sisa Pembayaran</label>
                        <input type="text" id="modal_sisa_pembayaran" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="modal_jumlah_bayar">Jumlah Bayar</label>
                        <input type="number" name="jumlah_bayar" id="modal_jumlah_bayar" class="form-control" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="metode_pembayaran">Cara Bayar</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="metode_tunai" value="tunai" checked>
                                <label class="form-check-label" for="metode_tunai">Tunai</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="metode_pembayaran" id="metode_transfer" value="transfer_bank">
                                <label class="form-check-label" for="metode_transfer">Transfer Bank</label>
                            </div>
                        </div>
                    </div>

                    <div id="transfer_fields" style="display: none;">
                        <div class="form-group">
                            <label for="rekening_id">Pilih Bank</label>
                            <select name="rekening_id" id="rekening_id" class="form-control @error('rekening_id') is-invalid @enderror">
                                <option value="">Pilih Rekening Bank</option>
                                @foreach($rekening ?? [] as $rek) 
                                    <option value="{{ $rek->id }}">{{ $rek->bank }} - {{ $rek->nomor_rekening }} ({{ $rek->atas_nama }})</option>
                                @endforeach
                            </select>
                            @error('rekening_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="bukti_pembayaran">Upload Bukti Pembayaran</label>
                            <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="form-control-file @error('bukti_pembayaran') is-invalid @enderror">
                            @error('bukti_pembayaran')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan_pembayaran">Keterangan (Opsional)</label>
                        <textarea name="keterangan_pembayaran" id="keterangan_pembayaran" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Pelunasan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, no_transaksi) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus transaksi: <strong>${no_transaksi}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(`/transaksi/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        Toast.fire({ icon: 'success', title: data.success });
                        document.getElementById(`transaksi-row-${id}`).remove();
                    } else {
                        Swal.fire('Gagal!', data.error || 'Gagal menghapus data.', 'error');
                    }
                })
                .catch(error => Swal.fire('Error!', 'Tidak dapat memproses permintaan.', 'error'));
            }
        });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function showPelunasanModal(transaksiId, sisaPembayaran, totalTransaksi, diskonTransaksi) {
        document.getElementById('formPelunasan').reset();
        document.getElementById('modal_transaksi_id').value = transaksiId;
        document.getElementById('modal_total_harus_dibayar').value = formatRupiah(parseFloat(totalTransaksi));
        document.getElementById('modal_sisa_pembayaran').value = formatRupiah(parseFloat(sisaPembayaran));
        document.getElementById('modal_jumlah_bayar').value = parseFloat(sisaPembayaran);
        
        document.getElementById('formPelunasan').action = `/transaksi/${transaksiId}/pelunasan`;
        
        togglePaymentFields();
        $('#pelunasanModal').modal('show');
    }

    const metodePembayaranRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
    const transferFieldsDiv = document.getElementById('transfer_fields');
    const buktiPembayaranInput = document.getElementById('bukti_pembayaran');
    const rekeningIdSelect = document.getElementById('rekening_id');

    function togglePaymentFields() {
        // Cek dulu apakah elemennya ada sebelum mengakses value-nya
        const selectedMethodRadio = document.querySelector('input[name="metode_pembayaran"]:checked');
        if (!selectedMethodRadio) return; // Keluar jika tidak ada yang terpilih

        const selectedMethod = selectedMethodRadio.value;
        transferFieldsDiv.style.display = 'none';
        
        // Pastikan elemen ada sebelum mengubah atribut
        if (buktiPembayaranInput) buktiPembayaranInput.removeAttribute('required');
        if (rekeningIdSelect) rekeningIdSelect.removeAttribute('required');

        if (selectedMethod === 'transfer_bank') {
            transferFieldsDiv.style.display = 'block';
            if (buktiPembayaranInput) buktiPembayaranInput.setAttribute('required', 'required');
            if (rekeningIdSelect) rekeningIdSelect.setAttribute('required', 'required');
        }
    }

    metodePembayaranRadios.forEach(radio => {
        radio.addEventListener('change', togglePaymentFields);
    });
</script>
@endpush

