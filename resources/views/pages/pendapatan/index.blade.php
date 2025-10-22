@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-invoice-dollar mr-2"></i>Rincian Pendapatan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Rincian Pendapatan</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Laporan Laba Rugi (Pendapatan Bersih)
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" onclick="exportExcel()">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Form Filter --}}
                <div class="mb-4">
                    <form action="{{ route('pendapatan.index') }}" method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label for="search_query">Cari (No. Transaksi / Pelanggan)</label>
                                    <input type="text" name="search_query" id="search_query" class="form-control" placeholder="Masukkan kata kunci..." value="{{ request('search_query') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Dari Tanggal</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                                </div>
                            </div>
                             <div class="col-lg-2 col-md-6">
                                <div class="form-group">
                                    <label for="end_date">Sampai Tanggal</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <div class="form-group">
                                    <label for="tipe_transaksi">Tipe Transaksi</label>
                                    <select name="tipe_transaksi" id="tipe_transaksi" class="form-control">
                                        <option value="">Semua Tipe</option>
                                        <option value="jasa_produk" {{ request('tipe_transaksi') == 'jasa_produk' ? 'selected' : '' }}>Jasa / Produk</option>
                                        <option value="brilink" {{ request('tipe_transaksi') == 'brilink' ? 'selected' : '' }}>BRILink</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-12">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                                        <a href="{{ route('pendapatan.index') }}" class="btn btn-secondary ml-2" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>No. Transaksi</th>
                                <th>Tipe</th>
                                <th>Pelanggan</th>
                                <th>Tgl. Bayar</th>
                                <th class="text-right">Biaya Admin</th>
                                <th class="text-right">Uang Masuk</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendapatanTransaksi as $item)
                            <tr>
                                <td>{{ $loop->iteration + ($pendapatanTransaksi->currentPage() - 1) * $pendapatanTransaksi->perPage() }}</td>
                                <td>{{ $item->no_transaksi }}</td>
                                <td class="text-center">
                                    @if($item->tipe_transaksi == 'brilink')
                                        <span class="badge badge-info">BRILink</span>
                                    @else
                                        <span class="badge badge-secondary">Jasa</span>
                                    @endif
                                </td>
                                <td>{{ $item->pelanggan->nama ?? 'Umum' }}</td>
                                <td>{{ $item->updated_at->format('d M Y') }}</td>
                                <td class="text-right">
                                    @if($item->tipe_transaksi == 'brilink')
                                        Rp{{ number_format($item->detail_brilink['admin'] ?? 0, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">Rp{{ number_format($item->uang_muka, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('transaksi.edit', $item->id) }}" class="btn btn-info btn-sm" title="Lihat Detail Transaksi"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data pendapatan sesuai filter.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light">
                            @if(isset($totalBiayaAdmin) && $totalBiayaAdmin > 0)
                            <tr>
                                <th colspan="5" class="text-right font-weight-normal">Total Keuntungan Admin (Sesuai Filter):</th>
                                <th class="text-right">Rp{{ number_format($totalBiayaAdmin, 0, ',', '.') }}</th>
                                <th colspan="2"></th>
                            </tr>
                            @endif
                            <tr>
                                <th colspan="6" class="text-right font-weight-normal">Total Uang Masuk (Sesuai Filter):</th>
                                <th class="text-right" colspan="2">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-right font-weight-normal text-danger">Total Pengeluaran (Sesuai Filter):</th>
                                <th class="text-right text-danger" colspan="2">- Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}</th>
                            </tr>
                            <tr style="border-top: 2px solid #6c757d;">
                                <th colspan="6" class="text-right h5">PENDAPATAN BERSIH:</th>
                                <th class="text-right h5" colspan="2">Rp{{ number_format($pendapatanBersih, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                 <div class="d-flex justify-content-center mt-3">
                    {{ $pendapatanTransaksi->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportExcel() {
        const form = document.getElementById('filterForm');
        const queryString = new URLSearchParams(new FormData(form)).toString();
        window.open(`{{ route('pendapatan.export-excel') }}?${queryString}`, '_blank');
    }
</script>
@endpush

