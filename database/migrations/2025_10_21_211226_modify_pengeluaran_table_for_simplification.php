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
        Schema::table('pengeluaran', function (Blueprint $table) {
            // Mengubah kolom 'jumlah' dan 'harga' agar tidak wajib diisi (nullable)
            // dan memberinya nilai default 0 agar data lama tidak error.
            $table->integer('jumlah')->default(0)->nullable()->change();
            $table->decimal('harga', 15, 2)->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran', function (Blueprint $table) {
            // Mengembalikan seperti semula jika migrasi di-rollback
            $table->integer('jumlah')->nullable(false)->change();
            $table->decimal('harga', 15, 2)->nullable(false)->change();
        });
    }
};