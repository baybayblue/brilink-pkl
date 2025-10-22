<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi'; // Menentukan nama tabel di database

    protected $fillable = [
        'no_transaksi',
        'pelanggan_id',
        'tanggal_order',
        'tanggal_selesai',
        'total',
        'diskon',
        'sisa',
        'uang_muka',
        'status_pengerjaan',
        'metode_pembayaran', // Kolom Anda yang sudah ada
        'bukti_pembayaran',  // Kolom Anda yang sudah ada
        'rekening_id',       // Kolom Anda yang sudah ada
        'keterangan_pembayaran', // Kolom Anda yang sudah ada
        'id_pelunasan',      // Dari migrasi lama Anda

        // --- KOLOM BARU YANG DITAMBAHKAN ---
        'tipe_transaksi',
        'status_pembayaran',
        'detail_brilink',
        // ---------------------------------
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'tanggal_selesai' => 'date',
        'total' => 'decimal:2',
        'diskon' => 'decimal:2',
        'sisa' => 'decimal:2',

        // --- CASTS BARU UNTUK KOLOM JSON ---
        'detail_brilink' => 'array',
        // -----------------------------------
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function transaksiDetails()
    {
        return $this->hasMany(TransaksiDetail::class);
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class);
    }
}