<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaksi->no_transaksi }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Source+Code+Pro:wght@400;500&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            background-color: #e9ecef;
            color: #333;
        }

        .receipt-container {
            max-width: 320px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .header-company {
            text-align: center;
            margin-bottom: 15px;
        }

        .header-company img {
            max-width: 80px;
            margin-bottom: 10px;
        }

        .header-company h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .header-company p {
            margin: 2px 0;
            font-size: 11px;
            line-height: 1.4;
        }

        .divider {
            border-top: 1px dashed #888;
            margin: 15px 0;
        }

        .transaction-info {
            font-family: 'Source Code Pro', monospace;
            font-size: 11px;
        }

        .transaction-info .row>div {
            padding: 2px 5px;
        }

        .table-products {
            width: 100%;
            margin: 15px 0;
            font-size: 11px;
        }

        .table-products th {
            border-bottom: 1px dashed #888;
            padding-bottom: 5px;
            font-weight: 600;
        }

        .table-products td {
            padding: 5px 0;
            vertical-align: top;
        }

        .table-products .text-right {
            text-align: right;
        }

        .table-products .product-name {
            word-break: break-word;
        }

        .summary-table {
            width: 100%;
            font-size: 11px;
        }

        .summary-table td {
            padding: 2px 0;
        }

        .summary-table .total-label {
            font-weight: 600;
        }

        .summary-table .total-amount {
            font-weight: 600;
            font-size: 13px;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }

        .watermark {
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem;
            font-weight: 800;
            color: rgba(40, 167, 69, 0.15);
            z-index: 0;
            pointer-events: none;
            text-transform: uppercase;
        }

        .print-button-container {
            text-align: center;
            margin: 20px auto 40px;
        }

        @media print {
            body {
                background-color: #fff;
            }

            .receipt-container {
                margin: 0;
                box-shadow: none;
                max-width: 100%;
            }

            .print-button-container {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        @if ($transaksi->sisa <= 0)
            <div class="watermark">LUNAS</div>
        @endif

        <div class="header-company">
            @if ($perusahaan && $perusahaan->logo)
                <img src="{{ asset('storage/' . $perusahaan->logo) }}" alt="Logo Perusahaan">
            @endif
            <h5>{{ $perusahaan->nama_perusahaan ?? 'Nama Perusahaan Anda' }}</h5>
            <p>{{ $perusahaan->alamat ?? 'Alamat Perusahaan Anda' }}</p>
            <p>Telp: {{ $perusahaan->no_handphone ?? '-' }}</p>
        </div>

        <div class="divider"></div>

        <div class="transaction-info">
            <div class="row">
                <div class="col-5">No. Order</div>
                <div class="col-7">: {{ $transaksi->no_transaksi }}</div>
                <div class="col-5">Tanggal</div>
                <div class="col-7">: {{ $transaksi->tanggal_order->format('d/m/Y H:i') }}</div>
                <div class="col-5">Pelanggan</div>
                <div class="col-7">: {{ $transaksi->pelanggan->nama ?? 'Umum' }}</div>
                <div class="col-5">Kasir</div>
                <div class="col-7">: {{ Auth::user()->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- ======================================================= --}}
        {{--     MODIFIKASI UTAMA: MENAMPILKAN DETAIL BERDASARKAN TIPE --}}
        {{-- ======================================================= --}}
        <table class="table-products">
            <thead>
                <tr>
                    <th>Deskripsi</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($transaksi->tipe_transaksi == 'brilink')
                    {{-- TAMPILAN UNTUK BRILINK --}}
                    <tr>
                        <td class="product-name">
                            {{-- Menggabungkan jenis dan tujuan untuk deskripsi yang jelas --}}
                            {{ ucwords(str_replace('_', ' ', $transaksi->detail_brilink['jenis'] ?? 'Transaksi')) }}
                            {{ $transaksi->detail_brilink['bank_tujuan'] ? 'ke ' . $transaksi->detail_brilink['bank_tujuan'] : '' }}
                            <div style="font-family: 'Source Code Pro', monospace;">
                                {{ $transaksi->detail_brilink['no_rekening'] ?? '' }}
                            </div>
                        </td>
                        <td class="text-right">{{ number_format($transaksi->detail_brilink['nominal'] ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="product-name">
                            Biaya Admin
                        </td>
                        <td class="text-right">{{ number_format($transaksi->detail_brilink['admin'] ?? 0, 0, ',', '.') }}</td>
                    </tr>

                @else
                    {{-- TAMPILAN UNTUK JASA/PRODUK (KODE LAMA ANDA) --}}
                    @foreach ($transaksi->transaksiDetails as $detail)
                        <tr>
                            <td class="product-name">
                                {{ $detail->nama_produk }}
                                <div style="font-family: 'Source Code Pro', monospace;">{{ $detail->qty }}
                                    {{ $detail->satuan }} x {{ number_format($detail->harga, 0, ',', '.') }}</div>
                            </td>
                            <td class="text-right">{{ number_format($detail->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="divider"></div>

        {{-- ======================================================= --}}
        {{--     MODIFIKASI UTAMA: MENAMPILKAN SUMMARY BERDASARKAN TIPE --}}
        {{-- ======================================================= --}}
        <table class="summary-table">
            @if($transaksi->tipe_transaksi == 'brilink')
                {{-- SUMMARY SEDERHANA UNTUK BRILINK --}}
                <tr class="total-label">
                    <td>Total Bayar</td>
                    <td class="text-right total-amount">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</td>
                </tr>
            @else
                {{-- SUMMARY LENGKAP UNTUK JASA/PRODUK (KODE LAMA ANDA) --}}
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp{{ number_format($transaksi->total + $transaksi->diskon, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Diskon</td>
                    <td class="text-right">Rp{{ number_format($transaksi->diskon, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-label">
                    <td>Total</td>
                    <td class="text-right total-amount">Rp{{ number_format($transaksi->total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Uang Muka/Bayar</td>
                    <td class="text-right">Rp{{ number_format($transaksi->uang_muka, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-label">
                    <td>Sisa</td>
                    <td class="text-right total-amount">Rp{{ number_format($transaksi->sisa, 0, ',', '.') }}</td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        <div class="footer-text">
            <p>Terima kasih atas kunjungan Anda!</p>
            @if($transaksi->tipe_transaksi != 'brilink')
                <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
            @endif
        </div>
    </div>

    <div class="print-button-container">
        <button class="btn btn-primary" onclick="window.print()"><i class="fas fa-print mr-2"></i>Cetak Struk
            Ini</button>
        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</body>

</html>
