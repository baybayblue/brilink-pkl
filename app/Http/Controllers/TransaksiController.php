<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran; // <-- DITAMBAHKAN
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Rekening;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransaksiExport;
use App\Exports\PendapatanExport;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar semua transaksi dengan filter dan paginasi.
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $searchQuery = $request->input('search_query');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $tipeTransaksi = $request->input('tipe_transaksi'); 

        $query = Transaksi::with(['pelanggan'])->latest();

        // Terapkan filter
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('no_transaksi', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('pelanggan', function ($subq) use ($searchQuery) {
                      $subq->where('nama', 'like', '%' . $searchQuery . '%');
                  });
            });
        }
        if ($startDate) {
            $query->whereDate('tanggal_order', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal_order', '<=', $endDate);
        }
        if ($tipeTransaksi) {
            $query->where('tipe_transaksi', $tipeTransaksi);
        }

        $totalKeseluruhanTransaksi = $query->clone()->sum('total');
        $totalPiutang = $query->clone()->sum('sisa');

        $transaksi = $query->paginate($limit)->withQueryString();
        
        $rekening = Rekening::all();

        return view('pages.transaksi.index', compact(
            'transaksi', 
            'totalKeseluruhanTransaksi', 
            'totalPiutang', 
            'rekening', 
            'searchQuery', 
            'startDate', 
            'endDate', 
            'limit'
        ));
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        $latestTransaksi = Transaksi::latest()->first();
        $nextNoTransaksi = $this->generateNoTransaksi($latestTransaksi ? $latestTransaksi->no_transaksi : null);

        $pelanggan = Pelanggan::all();
        $produks = Produk::all();

        return view('pages.transaksi.create', compact(
            'nextNoTransaksi',
            'pelanggan',
            'produks'
        ));
    }

    /**
     * Menyimpan transaksi baru ke database.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->input('tipe_transaksi') == 'brilink') {
                $this->validateAndStoreBrilink($request);
            } else {
                $this->validateAndStoreJasaProduk($request);
            }
            
            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil disimpan!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail transaksi (read-only).
     */
    public function show(int $id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'transaksiDetails.produk'])->findOrFail($id);
        return view('pages.transaksi.show', compact('transaksi'));
    }

    /**
     * Menampilkan form untuk mengedit transaksi.
     */
    public function edit(int $id)
    {
        $transaksi = Transaksi::with('transaksiDetails')->findOrFail($id);

        if ($transaksi->tipe_transaksi == 'brilink') {
            return redirect()->route('transaksi.index')->with('error', 'Transaksi BRILink tidak dapat di-edit.');
        }

        $pelanggan = Pelanggan::all();
        $produks = Produk::all();

        return view('pages.transaksi.edit', compact('transaksi', 'pelanggan', 'produks'));
    }

    /**
     * Memperbarui data transaksi di database.
     */
    public function update(Request $request, int $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->tipe_transaksi == 'brilink') {
            return redirect()->route('transaksi.index')->with('error', 'Transaksi BRILink tidak dapat di-edit.');
        }

        DB::beginTransaction();
        try {
            $this->cleanNumericInputs($request, [
                'total_keseluruhan', 'uang_muka', 'diskon', 'sisa'
            ]);
            $this->cleanNumericArrayInputs($request, ['harga', 'total_item']);

            $validated = $this->validateJasaProdukRequest($request, $transaksi->id);

            $sisaPembayaran = ($validated['total_keseluruhan'] - ($validated['uang_muka'] ?? 0) - ($validated['diskon'] ?? 0));
            
            $transaksi->update([
                'no_transaksi' => $validated['no_transaksi'],
                'pelanggan_id' => $validated['pelanggan_id'],
                'tanggal_order' => $validated['tanggal_order'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'total' => $validated['total_keseluruhan'],
                'uang_muka' => $validated['uang_muka'] ?? 0,
                'diskon' => $validated['diskon'] ?? 0,
                'sisa' => $sisaPembayaran < 0 ? 0 : $sisaPembayaran,
                'status_pengerjaan' => $validated['status_pengerjaan'],
                'status_pembayaran' => ($sisaPembayaran <= 0) ? 'lunas' : 'belum_lunas',
            ]);

            $transaksi->transaksiDetails()->delete();
            $this->createTransaksiDetails($request, $transaksi);

            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil diperbarui!');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Memproses pembayaran pelunasan.
     */
    public function pelunasan(Request $request, int $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        $validated = $request->validate([
            'jumlah_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:tunai,transfer_bank',
            'rekening_id' => 'required_if:metode_pembayaran,transfer_bank|nullable|exists:rekening,id', 
            'bukti_pembayaran' => 'required_if:metode_pembayaran,transfer_bank|nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'keterangan_pembayaran' => 'nullable|string|max:500',
        ]);

        $totalTagihan = $transaksi->total - $transaksi->diskon;
        if (($transaksi->uang_muka + $validated['jumlah_bayar']) > $totalTagihan) {
            $kelebihan = ($transaksi->uang_muka + $validated['jumlah_bayar']) - $totalTagihan;
            return redirect()->back()->with('error', 'Jumlah pembayaran melebihi total tagihan. Kelebihan: Rp' . number_format($kelebihan, 0, ',', '.'));
        }

        DB::beginTransaction();
        try {
            $newUangMuka = $transaksi->uang_muka + $validated['jumlah_bayar'];
            $newSisa = $totalTagihan - $newUangMuka;

            $pathBuktiPembayaran = $transaksi->bukti_pembayaran;
            if ($request->hasFile('bukti_pembayaran')) {
                if ($pathBuktiPembayaran) Storage::disk('public')->delete($pathBuktiPembayaran);
                $pathBuktiPembayaran = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');
            }

            $transaksi->update([
                'uang_muka' => $newUangMuka,
                'sisa' => $newSisa < 0 ? 0 : $newSisa,
                'status_pembayaran' => ($newSisa <= 0) ? 'lunas' : 'belum_lunas',
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'bukti_pembayaran' => $pathBuktiPembayaran,
                'rekening_id' => $validated['rekening_id'] ?? null,
                'keterangan_pembayaran' => $validated['keterangan_pembayaran'] ?? null,
            ]);

            DB::commit();
            return redirect()->route('transaksi.index')->with('success', 'Pembayaran pelunasan berhasil diproses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menghapus transaksi dari database.
     */
    public function destroy(int $id)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            if ($transaksi->bukti_pembayaran) {
                Storage::disk('public')->delete($transaksi->bukti_pembayaran);
            }
            $transaksi->transaksiDetails()->delete();
            $transaksi->delete();
            return response()->json(['success' => 'Transaksi berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus. Transaksi ini mungkin terkait dengan data lain.'], 500);
        }
    }
    
    /**
     * Menampilkan halaman laporan pendapatan dengan perhitungan pendapatan bersih.
     */
    public function pendapatanIndex(Request $request)
    {
        // 1. Query untuk Pendapatan (Uang Masuk)
        $pendapatanQuery = Transaksi::with(['pelanggan', 'rekening'])
            ->where(function ($q) {
                $q->where('status_pembayaran', 'lunas')
                  ->orWhere('uang_muka', '>', 0);
            })
            ->latest('updated_at');

        // Terapkan filter yang relevan untuk pendapatan
        $this->applyPendapatanFilters($pendapatanQuery, $request);

        // 2. Query untuk Pengeluaran (Uang Keluar)
        $pengeluaranQuery = Pengeluaran::query();
        
        // Terapkan filter tanggal yang sama ke pengeluaran
        $pengeluaranQuery->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->input('start_date')));
        $pengeluaranQuery->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->input('end_date')));
        
        // 3. Hitung Semua Total
        $allFilteredTransactions = (clone $pendapatanQuery)->get();
        
        $totalPendapatan = $allFilteredTransactions->sum('uang_muka');
        
        $totalBiayaAdmin = $allFilteredTransactions
            ->where('tipe_transaksi', 'brilink')
            ->sum(fn ($transaksi) => $transaksi->detail_brilink['admin'] ?? 0);

        $totalPengeluaran = $pengeluaranQuery->sum('total');

        $pendapatanBersih = $totalPendapatan - $totalPengeluaran;
        
        // 4. Terapkan paginasi untuk ditampilkan di tabel
        $pendapatanTransaksi = $pendapatanQuery->paginate(15)->withQueryString();
        $rekening = Rekening::all();

        return view('pages.pendapatan.index', compact(
            'pendapatanTransaksi',
            'totalPendapatan',
            'totalBiayaAdmin',
            'totalPengeluaran',
            'pendapatanBersih',
            'rekening'
        ));
    }

    // --- HELPER METHODS & OTHER PUBLIC METHODS ---

    public function getProdukItemRow(Request $request)
    {
        $index = $request->input('index', 0);
        $produks = Produk::all();
        return view('pages.transaksi.produk_item_row', compact('index', 'produks'));
    }

    public function piutangIndex()
    {
        $piutangTransaksi = Transaksi::with('pelanggan')
                                     ->where('sisa', '>', 0)
                                     ->where('tipe_transaksi', 'jasa_produk')
                                     ->latest()
                                     ->get();
        $totalPiutang = $piutangTransaksi->sum('sisa');
        return view('pages.piutang.index', compact('piutangTransaksi', 'totalPiutang'));
    }
    
    public function printReceipt(int $id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'transaksiDetails.produk'])->findOrFail($id);
        $perusahaan = Perusahaan::first();
        return view('pages.transaksi.receipt', compact('transaksi', 'perusahaan'));
    }
    
    public function printInvoice(int $id)
    {
        $transaksi = Transaksi::with(['pelanggan', 'transaksiDetails.produk'])->findOrFail($id);
        if ($transaksi->tipe_transaksi == 'brilink') {
            return redirect()->back()->with('error', 'Transaksi BRILink tidak memiliki Invoice.');
        }
        $perusahaan = Perusahaan::first();
        return view('pages.transaksi.invoice', compact('transaksi', 'perusahaan'));
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new TransaksiExport($request->query()), 'transaksi_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function exportExcelPendapatan(Request $request)
    {
        return Excel::download(new PendapatanExport($request->all()), 'laporan-pendapatan-' . now()->format('d-m-Y') . '.xlsx');
    }
    
    // --- PRIVATE HELPER METHODS ---

    private function validateAndStoreBrilink(Request $request): void
    {
        $validated = $request->validate([
            'no_transaksi' => 'required|string|unique:transaksi,no_transaksi|max:255',
            'pelanggan_id' => 'nullable|exists:pelanggan,id',
            'tanggal_order' => 'required|date',
            'tipe_transaksi' => 'required|in:jasa_produk,brilink',
            'jenis_transaksi_brilink' => 'required|string|max:255',
            'bank_tujuan' => 'nullable|string|max:255',
            'no_rekening_tujuan' => 'nullable|string|max:255',
            'nama_pemilik_rekening' => 'nullable|string|max:255',
            'nominal_brilink' => 'required|numeric|min:0',
            'biaya_admin_brilink' => 'required|numeric|min:0',
        ]);

        $totalBayar = $validated['nominal_brilink'] + $validated['biaya_admin_brilink'];
        
        Transaksi::create([
            'no_transaksi' => $validated['no_transaksi'],
            'pelanggan_id' => $validated['pelanggan_id'],
            'tanggal_order' => $validated['tanggal_order'],
            'total' => $totalBayar,
            'uang_muka' => $totalBayar,
            'sisa' => 0,
            'status_pengerjaan' => 'selesai',
            'tipe_transaksi' => 'brilink',
            'status_pembayaran' => 'lunas',
            'detail_brilink' => [
                'jenis' => $validated['jenis_transaksi_brilink'],
                'bank_tujuan' => $validated['bank_tujuan'],
                'no_rekening' => $validated['no_rekening_tujuan'],
                'atas_nama' => $validated['nama_pemilik_rekening'],
                'nominal' => $validated['nominal_brilink'],
                'admin' => $validated['biaya_admin_brilink'],
            ],
        ]);
    }
    
    private function validateAndStoreJasaProduk(Request $request): void
    {
        $this->cleanNumericInputs($request, ['total_keseluruhan', 'uang_muka', 'diskon', 'sisa']);
        $this->cleanNumericArrayInputs($request, ['harga', 'total_item']);
        
        $validated = $this->validateJasaProdukRequest($request);

        $sisaPembayaran = ($validated['total_keseluruhan'] - ($validated['uang_muka'] ?? 0) - ($validated['diskon'] ?? 0));

        $transaksi = Transaksi::create([
            'no_transaksi' => $validated['no_transaksi'],
            'pelanggan_id' => $validated['pelanggan_id'],
            'tanggal_order' => $validated['tanggal_order'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'total' => $validated['total_keseluruhan'],
            'uang_muka' => $validated['uang_muka'] ?? 0,
            'diskon' => $validated['diskon'] ?? 0,
            'sisa' => $sisaPembayaran < 0 ? 0 : $sisaPembayaran,
            'status_pengerjaan' => $validated['status_pengerjaan'],
            'tipe_transaksi' => 'jasa_produk',
            'status_pembayaran' => ($sisaPembayaran <= 0) ? 'lunas' : 'belum_lunas',
        ]);
        
        $this->createTransaksiDetails($request, $transaksi);
    }

    private function createTransaksiDetails(Request $request, Transaksi $transaksi): void
    {
        if ($request->has('nama_produk') && is_array($request->input('nama_produk'))) {
            foreach ($request->input('nama_produk') as $key => $nama_produk) {
                if (!empty($nama_produk)) {
                    TransaksiDetail::create([
                        'transaksi_id' => $transaksi->id,
                        'produk_id' => $request->input('produk_id.' . $key),
                        'nama_produk' => $nama_produk,
                        'keterangan' => $request->input('keterangan.' . $key),
                        'qty' => $request->input('qty.' . $key),
                        'ukuran' => $request->input('ukuran.' . $key),
                        'satuan' => $request->input('satuan.' . $key),
                        'harga' => $request->input('harga.' . $key),
                        'total' => $request->input('total_item.' . $key),
                    ]);
                }
            }
        }
    }

    private function applyPendapatanFilters($query, Request $request): void
    {
        $query->when($request->filled('search_query'), function ($q) use ($request) {
            $search = $request->input('search_query');
            $q->where(function ($subq) use ($search) {
                $subq->where('no_transaksi', 'like', "%{$search}%")
                     ->orWhereHas('pelanggan', fn($p) => $p->where('nama', 'like', "%{$search}%"));
            });
        });

        $query->when($request->filled('start_date'), fn($q) => $q->whereDate('updated_at', '>=', $request->input('start_date')));
        $query->when($request->filled('end_date'), fn($q) => $q->whereDate('updated_at', '<=', $request->input('end_date')));
        $query->when($request->filled('tipe_transaksi'), fn($q) => $q->where('tipe_transaksi', $request->input('tipe_transaksi')));
        $query->when($request->filled('metode_pembayaran') && $request->input('metode_pembayaran') !== 'all', fn($q) => $q->where('metode_pembayaran', $request->input('metode_pembayaran')));
        $query->when($request->input('metode_pembayaran') === 'transfer_bank' && $request->filled('rekening_id'), fn($q) => $q->where('rekening_id', $request->input('rekening_id')));
    }

    private function cleanNumericInputs(Request $request, array $fields): void
    {
        $cleaned = [];
        foreach ($fields as $field) {
            $cleaned[$field] = (float) str_replace(['Rp ', '.'], '', $request->input($field));
        }
        $request->merge($cleaned);
    }
    
    private function cleanNumericArrayInputs(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            if ($request->has($field) && is_array($request->input($field))) {
                $cleanedArray = [];
                foreach ($request->input($field) as $key => $value) {
                    $cleanedArray[$key] = (float) str_replace(['Rp ', '.'], '', $value);
                }
                $request->merge([$field => $cleanedArray]);
            }
        }
    }

    private function validateJasaProdukRequest(Request $request, ?int $transaksiId = null): array
    {
        $noTransaksiRule = 'required|string|max:255|unique:transaksi,no_transaksi';
        if ($transaksiId) {
            $noTransaksiRule .= ',' . $transaksiId;
        }

        $validated = $request->validate([
            'no_transaksi' => $noTransaksiRule,
            'pelanggan_id' => 'nullable|exists:pelanggan,id',
            'tanggal_order' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_order',
            'total_keseluruhan' => 'required|numeric|min:0',
            'uang_muka' => 'nullable|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'status_pengerjaan' => 'required|in:menunggu export,belum dikerjakan,proses desain,proses produksi,selesai',
        ]);
        
        $request->validate([
            'nama_produk.*' => 'required|string|max:255',
            'qty.*' => 'required|integer|min:1',
            'harga.*' => 'required|numeric|min:0',
            'total_item.*' => 'required|numeric|min:0',
        ]);
        
        return $validated;
    }

    private function generateNoTransaksi(?string $lastNoTransaksi): string
    {
        $prefix = 'KRP';
        $datePart = now()->format('ymd');
        $newNumber = 1;

        if ($lastNoTransaksi && str_starts_with($lastNoTransaksi, $prefix . $datePart)) {
            $lastNum = (int) substr($lastNoTransaksi, -3);
            $newNumber = $lastNum + 1;
        }

        return $prefix . $datePart . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}

