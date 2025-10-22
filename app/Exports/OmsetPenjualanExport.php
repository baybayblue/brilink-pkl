<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OmsetPenjualanExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithEvents
{
    protected Collection $omsetData;
    protected float $subtotalOmset;
    protected string $selectedMonth;
    private int $rowNumber = 0;

    /**
     * @param \Illuminate\Support\Collection $omsetData      Data gabungan omset produk dan BRILink.
     * @param float                            $subtotalOmset  Total keseluruhan omset.
     * @param string                           $selectedMonth  Bulan yang difilter (format Y-m).
     */
    public function __construct(Collection $omsetData, float $subtotalOmset, string $selectedMonth)
    {
        $this->omsetData = $omsetData;
        $this->subtotalOmset = $subtotalOmset;
        $this->selectedMonth = $selectedMonth;
    }

    /**
     * Mengembalikan koleksi data yang akan diekspor.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->omsetData;
    }

    /**
     * Mendefinisikan header untuk file Excel.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Produk / Layanan',
            'Jumlah Transaksi',
            'Total Omset (Keuntungan Admin)',
        ];
    }

    /**
     * Memetakan data untuk setiap baris di Excel.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row['nama_item'] ?? '-',
            $row['jumlah'] ?? 0,
            $row['total'] ?? 0,
        ];
    }

    /**
     * Menerapkan format angka pada kolom.
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER,       // Format untuk Jumlah
            'D' => '"Rp"#,##0',                     // Format Rupiah untuk Total Omset
        ];
    }

    /**
     * Menerapkan style pada header.
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Membuat header (baris 1) menjadi bold.
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Menambahkan baris total di akhir sheet.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Dapatkan baris terakhir setelah semua data ditulis
                $lastRow = $this->rowNumber + 2; // +1 untuk header, +1 untuk spasi

                // Hitung total keuntungan admin saja
                $totalAdminFee = $this->omsetData
                    ->where('tipe', 'brilink')
                    ->sum('total');
                
                // Tambahkan baris untuk total keuntungan admin jika ada
                if ($totalAdminFee > 0) {
                    $event->sheet->setCellValue("C{$lastRow}", 'Total Keuntungan Admin:');
                    $event->sheet->setCellValue("D{$lastRow}", $totalAdminFee);
                    $event->sheet->getStyle("C{$lastRow}:D{$lastRow}")->getFont()->setBold(true);
                    $lastRow++; // Pindah ke baris berikutnya
                }

                // Tambahkan baris untuk subtotal keseluruhan
                $event->sheet->setCellValue("C{$lastRow}", 'Total Omset Keseluruhan:');
                $event->sheet->setCellValue("D{$lastRow}", $this->subtotalOmset);
                $event->sheet->getStyle("C{$lastRow}:D{$lastRow}")->getFont()->setBold(true);

                // Menerapkan format Rupiah ke sel total
                $event->sheet->getStyle("D" . ($lastRow - 1))->getNumberFormat()->setFormatCode('"Rp"#,##0');
                $event->sheet->getStyle("D{$lastRow}")->getNumberFormat()->setFormatCode('"Rp"#,##0');
            },
        ];
    }
}
