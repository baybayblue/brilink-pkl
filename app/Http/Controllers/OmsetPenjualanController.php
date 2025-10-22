<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // <-- DITAMBAHKAN
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OmsetPenjualanExport; // Pastikan Anda membuat file Export ini

class OmsetPenjualanController extends Controller
{
    /**
     * Menampilkan laporan omset gabungan dari penjualan produk dan layanan BRILink.
     */
    public function index(Request $request)
    {
        // Data untuk filter dropdown
        $produks = Produk::orderBy('nama')->get();
        $layananBrilink = $this->getLayananBrilink();

        // Mengambil nilai filter dari request
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $itemFilter = $request->input('item_filter');

        // Mengambil dan menggabungkan data omset
        $omsetData = $this->getCombinedOmsetData($selectedMonth, $itemFilter);

        // Menghitung subtotal dari data yang sudah digabung
        $subtotalOmset = $omsetData->sum('total');

        return view('pages.omset.index', compact(
            'produks',
            'layananBrilink',
            'omsetData',
            'subtotalOmset',
            'selectedMonth'
        ));
    }

    /**
     * Menangani ekspor data omset ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $selectedMonth = $request->input('bulan', Carbon::now()->format('Y-m'));
        $itemFilter = $request->input('item_filter');

        $omsetData = $this->getCombinedOmsetData($selectedMonth, $itemFilter);
        $subtotalOmset = $omsetData->sum('total');
        
        $fileName = 'omset_penjualan_' . Carbon::parse($selectedMonth)->format('Y-m') . '.xlsx';

        // Anda perlu membuat file App\Exports\OmsetPenjualanExport
        // Jalankan: php artisan make:export OmsetPenjualanExport
        return Excel::download(new OmsetPenjualanExport($omsetData, $subtotalOmset, $selectedMonth), $fileName);
    }

    /**
     * Mengambil dan menggabungkan data omset dari produk dan BRILink.
     *
     * @param string $selectedMonth (Format: 'Y-m')
     * @param string|null $itemFilter (Format: 'produk-1' or 'brilink-transfer')
     * @return Collection
     */
    private function getCombinedOmsetData(string $selectedMonth, ?string $itemFilter): Collection
    {
        $carbonMonth = Carbon::parse($selectedMonth);
        $startOfMonth = $carbonMonth->startOfMonth()->toDateString();
        $endOfMonth = $carbonMonth->endOfMonth()->toDateString();

        $omsetProduk = new Collection();
        $omsetBrilink = new Collection();

        // Memisahkan tipe filter
        $filterType = null;
        $filterId = null;
        if ($itemFilter) {
            [$filterType, $filterId] = explode('-', $itemFilter, 2);
        }

        // 1. Ambil Omset dari Jasa/Produk jika filter tidak spesifik ke BRILink
        if ($filterType !== 'brilink') {
            $produkQuery = TransaksiDetail::with('transaksi')
                ->whereHas('transaksi', function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('tanggal_order', [$startOfMonth, $endOfMonth]);
                })
                ->selectRaw('nama_produk, SUM(qty) as jumlah, SUM(total) as total')
                ->groupBy('nama_produk');

            if ($filterType === 'produk' && $filterId) {
                $produkQuery->where('produk_id', $filterId);
            }

            $omsetProduk = $produkQuery->get()->map(function ($item) {
                return [
                    'nama_item' => $item->nama_produk,
                    'jumlah' => $item->jumlah,
                    'total' => $item->total,
                    'tipe' => 'jasa_produk',
                ];
            });
        }

        // 2. Ambil Omset (Keuntungan Admin) dari BRILink jika filter tidak spesifik ke Produk
        if ($filterType !== 'produk') {
            $brilinkQuery = Transaksi::where('tipe_transaksi', 'brilink')
                ->whereBetween('tanggal_order', [$startOfMonth, $endOfMonth]);

            if ($filterType === 'brilink' && $filterId) {
                // JSON_EXTRACT lebih efisien untuk query JSON di MySQL
                $brilinkQuery->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(detail_brilink, '$.jenis'))"), $filterId);
            }

            $omsetBrilink = $brilinkQuery->get()
                ->groupBy('detail_brilink.jenis')
                ->map(function ($transaksis, $jenis) {
                    return [
                        'nama_item' => ucwords(str_replace('_', ' ', $jenis)),
                        'jumlah' => $transaksis->count(),
                        'total' => $transaksis->sum(fn($t) => $t->detail_brilink['admin'] ?? 0),
                        'tipe' => 'brilink',
                    ];
                });
        }
        
        // 3. Gabungkan kedua koleksi dan urutkan berdasarkan total omset
        // --- MODIFIKASI: Mengganti merge() dengan concat() untuk menghindari error ---
        return $omsetProduk->concat($omsetBrilink->values())->sortByDesc('total');
    }

    /**
     * Mendapatkan daftar layanan BRILink untuk filter.
     *
     * @return array
     */
    private function getLayananBrilink(): array
    {
        return [
            ['id' => 'tarik_tunai', 'nama' => 'Tarik Tunai'],
            ['id' => 'transfer', 'nama' => 'Transfer'],
            ['id' => 'setor_tunai', 'nama' => 'Setor Tunai'],
            ['id' => 'pembayaran_tagihan', 'nama' => 'Pembayaran Tagihan'],
            ['id' => 'lainnya', 'nama' => 'Lainnya'],
        ];
    }
}

