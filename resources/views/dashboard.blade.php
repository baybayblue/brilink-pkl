@extends('layouts.app')

@push('styles')
<style>
    /* Memberikan tinggi yang konsisten untuk info-box */
    .info-box {
        min-height: 120px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-radius: .5rem;
    }
    .info-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    .info-box-content {
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
        font-size: 2rem !important;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #007bff, #6610f2) !important; }
    .bg-gradient-info { background: linear-gradient(135deg, #17a2b8, #27c8a9) !important; }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745, #218838) !important; }
    .bg-gradient-warning { background: linear-gradient(135deg, #ffc107, #fd7e14) !important; color: #fff !important; }
    .bg-gradient-danger { background: linear-gradient(135deg, #dc3545, #c82333) !important; }
    .bg-gradient-secondary { background: linear-gradient(135deg, #6c757d, #343a40) !important; }
</style>
@endpush

@section('content_header')
    {{-- Sapaan personal dan tanggal --}}
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Selamat Datang, {{ Auth::user()->username ?? 'Pengguna' }}!</h1>
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
    {{-- Baris untuk Info Box --}}
    <div class="row">
        {{-- Total Omset Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-success"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Omset Hari Ini</span>
                    <span class="info-box-number">Rp{{ number_format($totalOmsetHariIni, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- Total Pengeluaran Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-danger"><i class="fas fa-arrow-circle-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pengeluaran Hari Ini</span>
                    <span class="info-box-number">Rp{{ number_format($totalPengeluaranHariIni, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        {{-- Total Konsumen --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-warning"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Konsumen</span>
                    <span class="info-box-number">{{ $totalKonsumen }}</span>
                </div>
            </div>
        </div>
        {{-- Orderan Hari Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-info"><i class="fas fa-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Orderan Hari Ini</span>
                    <span class="info-box-number">{{ $orderanHariIni }}</span>
                </div>
            </div>
        </div>
        {{-- Orderan Bulan Ini --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-secondary"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Orderan Bulan Ini</span>
                    <span class="info-box-number">{{ $orderanBulanIni }}</span>
                </div>
            </div>
        </div>
        {{-- Orderan Total --}}
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-gradient-primary"><i class="fas fa-chart-pie"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Orderan Total</span>
                    <span class="info-box-number">{{ $totalOrderan }}</span>
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
                                        @php
                                            $statusClass = $transaction->sisa == 0 ? 'badge-success' : ($transaction->uang_muka > 0 ? 'badge-warning' : 'badge-danger');
                                            $statusText = $transaction->sisa == 0 ? 'Lunas' : ($transaction->uang_muka > 0 ? 'DP' : 'Belum Lunas');
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center py-4">Tidak ada transaksi terbaru.</td>
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
                    label: 'Pendapatan',
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
