@extends('layouts.app')

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-arrow-circle-down mr-2"></i>Data Pengeluaran</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pengeluaran</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list-alt mr-2"></i>Daftar Pengeluaran
                </h3>
                <div class="card-tools">
                    <a href="{{ route('pengeluaran.export.excel', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
                    </a>
                    <a href="{{ route('pengeluaran.create') }}" class="btn btn-primary btn-sm ml-2">
                        <i class="fas fa-plus mr-1"></i> Tambah Pengeluaran
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Form Filter --}}
                <form action="{{ route('pengeluaran.index') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label for="search_query">Cari Keterangan</label>
                                <input type="text" name="search_query" id="search_query" class="form-control" placeholder="Masukkan keterangan..." value="{{ request('search_query') }}">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label for="jenis_pengeluaran">Jenis Pengeluaran</label>
                                <select name="jenis_pengeluaran" id="jenis_pengeluaran" class="form-control">
                                    <option value="">Semua Jenis</option>
                                    {{-- Opsi disesuaikan dengan form create/edit --}}
                                    <option value="Uang Makan" {{ request('jenis_pengeluaran') == 'Uang Makan' ? 'selected' : '' }}>Uang Makan</option>
                                    <option value="Token Listrik" {{ request('jenis_pengeluaran') == 'Token Listrik' ? 'selected' : '' }}>Token Listrik</option>
                                    <option value="Air PDAM" {{ request('jenis_pengeluaran') == 'Air PDAM' ? 'selected' : '' }}>Air PDAM</option>
                                    <option value="Modal" {{ request('jenis_pengeluaran') == 'Modal' ? 'selected' : '' }}>Modal</option>
                                    <option value="Gaji Karyawan" {{ request('jenis_pengeluaran') == 'Gaji Karyawan' ? 'selected' : '' }}>Gaji Karyawan</option>
                                    <option value="Beli Bahan" {{ request('jenis_pengeluaran') == 'Beli Bahan' ? 'selected' : '' }}>Beli Bahan</option>
                                    <option value="Sumbangan" {{ request('jenis_pengeluaran') == 'Sumbangan' ? 'selected' : '' }}>Sumbangan</option>
                                    <option value="Paket COD" {{ request('jenis_pengeluaran') == 'Paket COD' ? 'selected' : '' }}>Paket COD</option>
                                    <option value="Perlengkapan" {{ request('jenis_pengeluaran') == 'Perlengkapan' ? 'selected' : '' }}>Perlengkapan</option>
                                    <option value="Transportasi" {{ request('jenis_pengeluaran') == 'Transportasi' ? 'selected' : '' }}>Transportasi</option>
                                    <option value="Lain-lain" {{ request('jenis_pengeluaran') == 'Lain-lain' ? 'selected' : '' }}>Lain-lain</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <div class="form-group">
                                <label for="start_date">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                             <div class="form-group">
                                <label for="end_date">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-12">
                             <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-search"></i> Cari</button>
                                    <a href="{{ route('pengeluaran.index') }}" class="btn btn-secondary ml-2" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th class="text-right">Total</th>
                                <th>Tanggal</th>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pengeluaran as $item)
                            <tr id="pengeluaran-row-{{ $item->id }}">
                                <td>{{ $loop->iteration + ($pengeluaran->currentPage() - 1) * $pengeluaran->perPage() }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ $item->jenis_pengeluaran }}</span>
                                </td>
                                <td class="text-right"><strong>Rp{{ number_format($item->total, 0, ',', '.') }}</strong></td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('pengeluaran.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $item->id }}', '{{ $item->keterangan }}')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data pengeluaran ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($pengeluaran->isNotEmpty())
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="3" class="text-right">Total Pengeluaran (Sesuai Filter):</th>
                                <th class="text-right" colspan="3">Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $pengeluaran->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id, keterangan) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus pengeluaran: <strong>${keterangan}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(`/pengeluaran/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        Toast.fire({ icon: 'success', title: data.success });
                        document.getElementById(`pengeluaran-row-${id}`).remove();
                    } else {
                        Swal.fire('Gagal!', data.error || 'Gagal menghapus data.', 'error');
                    }
                })
                .catch(error => Swal.fire('Error!', 'Tidak dapat memproses permintaan.', 'error'));
            }
        });
    }
</script>
@endpush
