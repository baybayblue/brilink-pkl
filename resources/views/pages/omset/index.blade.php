@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        {{-- MODIFIKASI: Judul diubah menjadi lebih umum --}}
        <h1 class="m-0"><i class="fas fa-chart-line mr-2"></i>Laporan Omset Penjualan</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan Omset</li>
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
                    <i class="fas fa-chart-bar mr-1"></i>
                    Laporan Omset (Produk & Layanan BRILink)
                </h3>
            </div>
            <div class="card-body">
                {{-- Form Filter yang Dirapikan --}}
                <div class="mb-4">
                    <form action="{{ route('omset.index') }}" method="GET" id="omsetFilterForm">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-group">
                                    {{-- MODIFIKASI: Label filter diubah --}}
                                    <label for="item_filter">Filter Produk / Layanan</label>
                                    {{-- MODIFIKASI: Select diubah untuk menangani produk dan layanan --}}
                                    <select name="item_filter" id="item_filter" class="form-control">
                                        <option value="">Semua Produk & Layanan</option>
                                        <optgroup label="Jasa / Produk">
                                            {{-- Asumsi controller mengirim $produks --}}
                                            @foreach ($produks as $produk)
                                                <option value="produk-{{ $produk->id }}" {{ (request('item_filter') ?? '') == 'produk-'.$produk->id ? 'selected' : '' }}>
                                                    {{ $produk->nama }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Layanan BRILink">
                                             {{-- Asumsi controller mengirim $layananBrilink --}}
                                            @foreach ($layananBrilink as $layanan)
                                                <option value="brilink-{{ $layanan['id'] }}" {{ (request('item_filter') ?? '') == 'brilink-'.$layanan['id'] ? 'selected' : '' }}>
                                                    {{ $layanan['nama'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="form-group">
                                    <label for="bulan">Filter Bulan</label>
                                    <input type="month" name="bulan" id="bulan" class="form-control" value="{{ $selectedMonth ?? '' }}">
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-12">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter mr-1"></i> Filter</button>
                                        <a href="{{ route('omset.index') }}" class="btn btn-secondary ml-2" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                                        <button type="button" class="btn btn-success ml-2" onclick="exportOmsetExcel()"><i class="fas fa-file-excel mr-1"></i> Export Omset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Tabel Data --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                {{-- MODIFIKASI: Header kolom diubah --}}
                                <th>Produk / Layanan</th>
                                <th class="text-center">Jumlah Transaksi</th>
                                {{-- MODIFIKASI: Header kolom diubah --}}
                                <th class="text-right">Total Omset (Keuntungan Admin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- MODIFIKASI: Variabel diubah menjadi lebih generik ($omsetData) --}}
                            @forelse ($omsetData as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{-- Asumsi controller mengirim 'nama_item' --}}
                                    {{ $data['nama_item'] }}
                                    {{-- Tambahkan badge untuk membedakan --}}
                                    @if($data['tipe'] == 'brilink')
                                        <span class="badge badge-info ml-2">BRILink</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $data['jumlah'] }}</td>
                                {{-- Untuk BRILink, 'total' adalah sum dari admin fee --}}
                                <td class="text-right">Rp{{ number_format($data['total'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data omset penjualan untuk periode ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        {{-- MODIFIKASI: Variabel diubah menjadi lebih generik --}}
                        @if(!empty($omsetData) && count($omsetData) > 0)
                        <tfoot class="bg-light">
                            <tr>
                                {{-- MODIFIKASI: Label diubah --}}
                                <th colspan="3" class="text-right">Total Omset (Produk + Keuntungan Admin):</th>
                                <th class="text-right">Rp{{ number_format($subtotalOmset, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportOmsetExcel() {
        const form = document.getElementById('omsetFilterForm');
        const queryString = new URLSearchParams(new FormData(form)).toString();
        // Pastikan Anda membuat route 'omset.export-excel'
        window.location.href = `{{ route('omset.export-excel') }}?${queryString}`;
    }
</script>
@endpush

