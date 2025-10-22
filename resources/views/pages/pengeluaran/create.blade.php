@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark"><i class="fas fa-file-invoice-dollar"></i> Tambah Pengeluaran Baru</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pengeluaran.index') }}">Data Pengeluaran</a></li>
            <li class="breadcrumb-item active">Tambah Pengeluaran</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Form Pengeluaran</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('pengeluaran.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_pengeluaran">Jenis Pengeluaran <span class="text-danger">*</span></label>
                                <select name="jenis_pengeluaran" id="jenis_pengeluaran" class="form-control @error('jenis_pengeluaran') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis Pengeluaran</option>
                                    {{-- Opsi Kasbon Karyawan dihapus --}}
                                    <option value="Uang Makan" {{ old('jenis_pengeluaran') == 'Uang Makan' ? 'selected' : '' }}>Uang Makan</option>
                                    <option value="Token Listrik" {{ old('jenis_pengeluaran') == 'Token Listrik' ? 'selected' : '' }}>Token Listrik</option>
                                    <option value="Air PDAM" {{ old('jenis_pengeluaran') == 'Air PDAM' ? 'selected' : '' }}>Air PDAM</option>
                                    <option value="Modal" {{ old('jenis_pengeluaran') == 'Modal' ? 'selected' : '' }}>Modal</option>
                                    <option value="Gaji Karyawan" {{ old('jenis_pengeluaran') == 'Gaji Karyawan' ? 'selected' : '' }}>Gaji Karyawan</option>
                                    <option value="Beli Bahan" {{ old('jenis_pengeluaran') == 'Beli Bahan' ? 'selected' : '' }}>Beli Bahan</option>
                                    <option value="Sumbangan" {{ old('jenis_pengeluaran') == 'Sumbangan' ? 'selected' : '' }}>Sumbangan</option>
                                    <option value="Paket COD" {{ old('jenis_pengeluaran') == 'Paket COD' ? 'selected' : '' }}>Paket COD</option>
                                    <option value="Perlengkapan" {{ old('jenis_pengeluaran') == 'Perlengkapan' ? 'selected' : '' }}>Perlengkapan</option>
                                    <option value="Transportasi" {{ old('jenis_pengeluaran') == 'Transportasi' ? 'selected' : '' }}>Transportasi</option>
                                    <option value="Lain-lain" {{ old('jenis_pengeluaran') == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                                </select>
                                @error('jenis_pengeluaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="total">Total Pengeluaran <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" name="total" id="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total', 0) }}" min="0" step="0.01" required placeholder="Masukkan total biaya">
                                </div>
                                @error('total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Masukkan keterangan pengeluaran">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pengeluaran</button>
                        <a href="{{ route('pengeluaran.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Seluruh section 'scripts' dihapus karena tidak lagi diperlukan --}}

