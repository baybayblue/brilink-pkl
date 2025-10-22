@extends('layouts.app')

@push('styles')
<style>
    /* Memberikan tinggi yang konsisten untuk info-box */
    .info-box {
        min-height: 120px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-radius: .5rem;
        display: flex;
        flex-direction: column;
    }
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    .info-box .info-box-content {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    /* Menambahkan gradien pada ikon info-box */
    .info-box-icon {
        border-radius: .5rem 0 0 .5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem !important;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #6610f2) !important; color: #fff; }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #27c8a9) !important; color: #fff; }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745, #218838) !important; color: #fff; }
    .bg-gradient-teal { background: linear-gradient(135deg, #20c997, #1fab89) !important; color: #fff; }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #fd7e14) !important; color: #fff !important; }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545, #c82333) !important; color: #fff; }
    .bg-gradient-secondary { background: linear-gradient(135deg, #6c757d, #343a40) !important; color: #fff; }
</style>
@endpush

@section('content_header')
    {{-- Sapaan personal dan tanggal --}}
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Selamat Datang, {{ Auth::user()->name ?? 'Pengguna' }}!</h1>
            <small class="text-muted">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</small>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    {{-- Baris untuk Info Box yang Telah Disesuaikan --}}
    <div class="row">
        {{-- Total Pendapatan Hari Ini (Uang Masuk) --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendapatan Hari Ini (Uang Masuk)</span>
                    <span class="info-box-number">Rp{{ number_format($pendapatanHariIni ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- Keuntungan Admin BRILink Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-info"><i class="fas fa-university"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Keuntungan Admin BRILink (Hari Ini)</span>
                    <span class="info-box-number">Rp{{ number_format($keuntunganAdminHariIni ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- Total Piutang Aktif --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Piutang Aktif</span>
                    <span class="info-box-number">Rp{{ number_format($totalPiutang ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- Total Orderan Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transaksi Hari Ini</span>
                    <span class="info-box-number">{{ $totalOrderanHariIni ?? 0 }}</span>
                </div>
            </div>
        </div>
        {{-- Orderan Jasa/Produk Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-secondary"><i class="fas fa-box-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Order Jasa/Produk (Hari Ini)</span>
                    <span class="info-box-number">{{ $orderanJasaHariIni ?? 0 }}</span>
                </div>
            </div>
        </div>
        {{-- Transaksi BRILink Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-teal"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Transaksi BRILink (Hari Ini)</span>
                    <span class="info-box-number">{{ $transaksiBrilinkHariIni ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        {{-- Kolom Utama untuk Grafik --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-chart-area mr-2"></i>Ringkasan Performa Bulanan</h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="performanceChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Samping untuk Transaksi Terbaru --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header border-0">
                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Transaksi Terbaru</h3>
                    <div class="card-tools">
                        <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover m-0">
                            <tbody>
                                @forelse ($recentTransactions as $transaction)
                                <tr>
                                    <td>
                                        <a href="{{ route('transaksi.edit', $transaction->id) }}">{{ $transaction->no_transaksi }}</a>
                                        <small class="d-block text-muted">{{ $transaction->pelanggan->nama ?? 'Umum' }}</small>
                                    </td>
                                    <td class="text-right">
                                        @if($transaction->tipe_transaksi == 'brilink')
                                            <span class="badge badge-info">BRILink</span>
                                        @else
                                            @php
                                                $statusClass = $transaction->sisa <= 0 ? 'badge-success' : ($transaction->uang_muka > 0 ? 'badge-warning' : 'badge-danger');
                                                $statusText = $transaction->sisa <= 0 ? 'Lunas' : ($transaction->uang_muka > 0 ? 'DP' : 'Belum Lunas');
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center py-4" colspan="2">Tidak ada transaksi terbaru.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Memuat library Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk grafik (pastikan variabel ini dikirim dari controller)
    const labels = @json($monthlyIncomeLabels ?? []);
    const incomeData = @json($monthlyIncomeValues ?? []);
    const transactionData = @json($monthlyTransactionCounts ?? []);
    const ctx = document.getElementById('performanceChart').getContext('2d');

    const config = {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pendapatan (Uang Masuk)',
                    data: incomeData,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    yAxisID: 'y',
                    order: 2
                },
                {
                    label: 'Jumlah Transaksi',
                    data: transactionData,
                    borderColor: 'rgba(255, 193, 7, 1)',
                    backgroundColor: 'rgba(255, 193, 7, 0.2)',
                    type: 'line',
                    yAxisID: 'y1',
                    order: 1,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi'
                    },
                    grid: {
                        drawOnChartArea: false, // only want the grid lines for one axis to show up
                    },
                }
            }
        }
    };

    const performanceChart = new Chart(ctx, config);
});
</script>
@endpush

