<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
// Model Karyawan tidak lagi diperlukan di sini
// use App\Models\Karyawan; 
use Illuminate\Http\Request;
use App\Exports\PengeluaranExport;
use Maatwebsite\Excel\Facades\Excel;

class PengeluaranController extends Controller
{
    /**
     * Menampilkan daftar semua pengeluaran dengan filter dan paginasi.
     */
    public function index(Request $request)
    {
        $query = Pengeluaran::latest();

        // Terapkan filter secara kondisional
        $query->when($request->filled('search_query'), function ($q) use ($request) {
            $q->where('keterangan', 'like', '%' . $request->input('search_query') . '%');
        });

        $query->when($request->filled('jenis_pengeluaran'), function ($q) use ($request) {
            $q->where('jenis_pengeluaran', $request->input('jenis_pengeluaran'));
        });

        $query->when($request->filled('start_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->input('start_date'));
        });

        $query->when($request->filled('end_date'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->input('end_date'));
        });

        // HITUNG TOTAL dari semua data yang terfilter (sebelum paginasi)
        $totalPengeluaran = $query->clone()->sum('total');

        // Terapkan paginasi
        $pengeluaran = $query->paginate(10)->withQueryString();

        return view('pages.pengeluaran.index', compact('pengeluaran', 'totalPengeluaran'));
    }

    /**
     * Menampilkan form untuk membuat pengeluaran baru.
     */
    public function create()
    {
        // Tidak perlu lagi mengirim data karyawan
        return view('pages.pengeluaran.create');
    }

    /**
     * Menyimpan pengeluaran baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi disederhanakan, tidak ada lagi jumlah, harga, dan karyawan
        $validatedData = $request->validate([
            'jenis_pengeluaran' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'jenis_pengeluaran.required' => 'Jenis Pengeluaran wajib diisi.',
            'total.required' => 'Total Pengeluaran wajib diisi.',
            'total.numeric' => 'Total Pengeluaran harus berupa angka.',
        ]);

        Pengeluaran::create($validatedData);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    /**
     * Menampilkan form untuk mengedit pengeluaran.
     */
    public function edit(int $id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        // Tidak perlu lagi mengirim data karyawan
        return view('pages.pengeluaran.edit', compact('pengeluaran'));
    }

    /**
     * Memperbarui pengeluaran di database.
     */
    public function update(Request $request, int $id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        // Validasi disederhanakan
        $validatedData = $request->validate([
            'jenis_pengeluaran' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'jenis_pengeluaran.required' => 'Jenis Pengeluaran wajib diisi.',
            'total.required' => 'Total Pengeluaran wajib diisi.',
            'total.numeric' => 'Total Pengeluaran harus berupa angka.',
        ]);

        $pengeluaran->update($validatedData);

        return redirect()->route('pengeluaran.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    /**
     * Menghapus pengeluaran tertentu dari database.
     */
    public function destroy(int $id)
    {
        try {
            $pengeluaran = Pengeluaran::findOrFail($id);
            $pengeluaran->delete();

            return response()->json([
                'success' => 'Pengeluaran berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus pengeluaran.',
            ], 500);
        }
    }

    /**
     * Mengekspor data pengeluaran ke file Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search_query', 'jenis_pengeluaran', 'start_date', 'end_date']);
        
        $fileName = 'Laporan-Pengeluaran-' . now()->format('Y-m-d') . '.xlsx';

        // Pastikan file App\Exports\PengeluaranExport sudah ada
        return Excel::download(new PengeluaranExport($filters), $fileName);
    }
}
