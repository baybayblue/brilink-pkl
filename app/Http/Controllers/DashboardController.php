<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pengeluaran; // <-- DITAMBAHKAN
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menyiapkan dan menampilkan data ringkasan untuk halaman dashboard.
     */
    public function index()
    {
        // --- 1. PERSIAPAN DATA UNTUK INFO BOX ---

        $today = Carbon::today();
        $transaksiHariIni = Transaksi::whereDate('created_at', $today)->get();

        $pendapatanHariIni = $transaksiHariIni->sum('uang_muka');
        $keuntunganAdminHariIni = $transaksiHariIni->where('tipe_transaksi', 'brilink')->sum(
            fn ($transaksi) => $transaksi->detail_brilink['admin'] ?? 0
        );
        $totalPengeluaranHariIni = Pengeluaran::whereDate('created_at', $today)->sum('total');
        $pendapatanBersihHariIni = $pendapatanHariIni - $totalPengeluaranHariIni;

        $totalOrderanHariIni = $transaksiHariIni->count();
        $orderanJasaHariIni = $transaksiHariIni->where('tipe_transaksi', 'jasa_produk')->count();
        $transaksiBrilinkHariIni = $transaksiHariIni->where('tipe_transaksi', 'brilink')->count();

        $totalPiutang = Transaksi::where('sisa', '>', 0)
            ->where('tipe_transaksi', 'jasa_produk')
            ->sum('sisa');

        // --- 2. PERSIAPAN DATA UNTUK GRAFIK PERFORMA (6 BULAN TERAKHIR) ---

        $monthlyStats = Transaksi::select(
                DB::raw('YEAR(created_at) as year, MONTH(created_at) as month'),
                DB::raw('SUM(uang_muka) as total_income'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $monthlyData = collect(range(5, 0))->mapWithKeys(function ($i) {
            $date = now()->subMonths($i);
            return [
                $date->format('Y-n') => [
                    'label' => $date->translatedFormat('M Y'),
                    'income' => 0,
                    'transactions' => 0,
                ]
            ];
        });

        // --- PERBAIKAN DI SINI ---
        // Isi data dari hasil query database menggunakan metode put()
        foreach ($monthlyStats as $stat) {
            $yearMonth = $stat->year . '-' . $stat->month;
            if ($monthlyData->has($yearMonth)) {
                // Ambil data array yang ada
                $data = $monthlyData->get($yearMonth);
                // Modifikasi array tersebut
                $data['income'] = $stat->total_income;
                $data['transactions'] = $stat->transaction_count;
                // Masukkan kembali array yang sudah dimodifikasi ke collection
                $monthlyData->put($yearMonth, $data);
            }
        }
        // --- AKHIR PERBAIKAN ---


        $monthlyIncomeLabels = $monthlyData->pluck('label')->values()->all();
        $monthlyIncomeValues = $monthlyData->pluck('income')->values()->all();
        $monthlyTransactionCounts = $monthlyData->pluck('transactions')->values()->all();

        // --- 3. AMBIL DATA TRANSAKSI TERBARU ---

        $recentTransactions = Transaksi::with('pelanggan')
            ->latest()
            ->take(7) 
            ->get();
        
        // --- 4. KIRIM SEMUA DATA KE VIEW ---

        return view('dashboard', compact(
            'pendapatanHariIni',
            'keuntunganAdminHariIni',
            'totalPengeluaranHariIni',
            'pendapatanBersihHariIni',
            'totalPiutang',
            'totalOrderanHariIni',
            'orderanJasaHariIni',
            'transaksiBrilinkHariIni',
            'recentTransactions',
            'monthlyIncomeLabels',
            'monthlyIncomeValues',
            'monthlyTransactionCounts'
        ));
    }
}

