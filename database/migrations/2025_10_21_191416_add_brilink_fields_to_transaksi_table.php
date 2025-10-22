<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            
            // 1. Kolom untuk membedakan 'jasa_produk' atau 'brilink'
            $table->string('tipe_transaksi', 30)->default('jasa_produk')->after('status_pengerjaan');
            
            // 2. Kolom untuk status bayar (dari file index pertama Anda)
            $table->string('status_pembayaran', 30)->default('belum_lunas')->after('tipe_transaksi');
            
            // 3. Kolom JSON untuk semua detail BRILink
            // Ini akan menyimpan: jenis_transaksi, bank_tujuan, no_rekening,
            // nama_pemilik, nominal_brilink, dan biaya_admin_brilink
            $table->json('detail_brilink')->nullable()->after('status_pembayaran');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Ini untuk membatalkan migrasi (jika diperlukan)
            $table->dropColumn(['tipe_transaksi', 'status_pembayaran', 'detail_brilink']);
        });
    }
};