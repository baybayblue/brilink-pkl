@extends('layouts.app')

@push('styles')
<style>
    /* Styling untuk area upload gambar yang lebih baik */
    .image-upload-container {
        border: 2px dashed #ced4da;
        border-radius: .25rem;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        position: relative;
        height: 220px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
        overflow: hidden;
    }
    .image-upload-container:hover {
        border-color: #007bff;
        background-color: #e9ecef;
    }
    .upload-prompt .upload-icon {
        font-size: 2.5rem;
        color: #6c757d;
    }
    .upload-prompt .upload-text {
        margin-top: 0.5rem;
        color: #6c757d;
    }
    .image-upload-container input[type="file"] {
        display: none;
    }
    .image-preview {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .card-tabs .nav-tabs .nav-link.active {
        background-color: #fff;
        border-bottom: 3px solid #007bff;
        color: #007bff;
        font-weight: 600;
    }
    .card-tabs .nav-tabs .nav-link {
        border-bottom: 3px solid transparent;
    }
</style>
@endpush

@section('content_header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="far fa-building mr-2"></i>Pengaturan Usaha</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengaturan Usaha</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<form id="perusahaan-form" action="{{ route('perusahaan.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-12">
            <div class="card card-tabs">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-info-umum-tab" data-toggle="pill" href="#tab-info-umum" role="tab" aria-controls="tab-info-umum" aria-selected="true">
                                <i class="fas fa-info-circle mr-2"></i>Informasi Umum
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-media-branding-tab" data-toggle="pill" href="#tab-media-branding" role="tab" aria-controls="tab-media-branding" aria-selected="false">
                                <i class="fas fa-image mr-2"></i>Media & Branding
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                        {{-- Tab 1: Informasi Umum --}}
                        <div class="tab-pane fade show active" id="tab-info-umum" role="tabpanel" aria-labelledby="tab-info-umum-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nama_perusahaan">Nama Usaha <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-building"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' untuk menangani nilai null --}}
                                            <input type="text" name="nama_perusahaan" id="nama_perusahaan" class="form-control @error('nama_perusahaan') is-invalid @enderror" value="{{ old('nama_perusahaan', $perusahaan->nama_perusahaan ?? '') }}" placeholder="Contoh: Agen BRILink Maju Jaya" required>
                                            @error('nama_perusahaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $perusahaan->email ?? '') }}" placeholder="contoh@email.com" required>
                                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_handphone">No. Handphone</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="text" name="no_handphone" id="no_handphone" class="form-control @error('no_handphone') is-invalid @enderror" value="{{ old('no_handphone', $perusahaan->no_handphone ?? '') }}" placeholder="081234567890">
                                            @error('no_handphone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="instagram">Instagram</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-instagram"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="text" name="instagram" id="instagram" class="form-control @error('instagram') is-invalid @enderror" value="{{ old('instagram', $perusahaan->instagram ?? '') }}" placeholder="Username tanpa '@'">
                                            @error('instagram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="facebook">Facebook</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-facebook-f"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="text" name="facebook" id="facebook" class="form-control @error('facebook') is-invalid @enderror" value="{{ old('facebook', $perusahaan->facebook ?? '') }}" placeholder="Username Facebook">
                                            @error('facebook')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="twitter">Twitter</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-twitter"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="text" name="twitter" id="twitter" class="form-control @error('twitter') is-invalid @enderror" value="{{ old('twitter', $perusahaan->twitter ?? '') }}" placeholder="Username Twitter tanpa '@'">
                                            @error('twitter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="youtube">YouTube</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend"><span class="input-group-text"><i class="fab fa-youtube"></i></span></div>
                                            {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                            <input type="text" name="youtube" id="youtube" class="form-control @error('youtube') is-invalid @enderror" value="{{ old('youtube', $perusahaan->youtube ?? '') }}" placeholder="Nama Channel YouTube">
                                            @error('youtube')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="alamat">Alamat</label>
                                        {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                        <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3" placeholder="Masukkan alamat lengkap usaha">{{ old('alamat', $perusahaan->alamat ?? '') }}</textarea>
                                        @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="maps_url">Link Sematan Google Maps</label>
                                        {{-- PERBAIKAN: Tambahkan '?? ""' --}}
                                        <textarea name="maps_url" id="maps_url" class="form-control @error('maps_url') is-invalid @enderror" rows="4" placeholder="Contoh: <iframe src=... ></iframe>">{{ old('maps_url', $perusahaan->maps_url ?? '') }}</textarea>
                                        @error('maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">
                                            Buka Google Maps > Cari lokasi Anda > Klik "Share" > Pilih "Embed a map" > Salin kode HTML dan tempel di sini.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 2: Media & Branding --}}
                        <div class="tab-pane fade" id="tab-media-branding" role="tabpanel" aria-labelledby="tab-media-branding-tab">
                            <div class="row">
                                @php
                                    // PERBAIKAN: Gunakan helper optional() agar aman jika $perusahaan null saat di-loop
                                    $perusahaanData = optional($perusahaan);
                                    
                                    $imageFields = [
                                        'logo' => ['label' => 'Logo Usaha', 'rekomendasi' => '1200x700 px'],
                                        'favicon' => ['label' => 'Favicon', 'rekomendasi' => '72x72 px'],
                                        'logo_login' => ['label' => 'Logo Halaman Login', 'rekomendasi' => '1200x700 px'],
                                        'qr_code' => ['label' => 'QR Code Pembayaran', 'rekomendasi' => '700x700 px'],
                                    ];
                                @endphp

                                @foreach ($imageFields as $field => $details)
                                <div class="col-md-4 mb-4">
                                    <label>{{ $details['label'] }}</label>
                                    <label for="{{ $field }}" class="image-upload-container">
                                        <input type="file" name="{{ $field }}" id="{{ $field }}" class="@error($field) is-invalid @enderror" onchange="previewImage(this, '{{ $field }}-preview', '{{ $field }}-prompt')">
                                        
                                        {{-- PERBAIKAN: Gunakan $perusahaanData yang sudah aman dari null --}}
                                        <div id="{{ $field }}-prompt" class="upload-prompt {{ $perusahaanData->{$field} ? 'd-none' : '' }}">
                                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                            <p class="upload-text">Klik untuk memilih gambar</p>
                                        </div>
                                        
                                        {{-- PERBAIKAN: Gunakan $perusahaanData yang sudah aman dari null --}}
                                        <img id="{{ $field }}-preview" src="{{ $perusahaanData->{$field} ? asset('storage/' . $perusahaanData->{$field}) : '' }}" class="image-preview {{ $perusahaanData->{$field} ? '' : 'd-none' }}">
                                    </label>
                                    <small class="form-text text-muted">Rekomendasi: {{ $details['rekomendasi'] }}, Maks 5MB.</small>
                                    @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function previewImage(input, previewId, promptId) {
        const preview = document.getElementById(previewId);
        const prompt = document.getElementById(promptId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                prompt.classList.add('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush