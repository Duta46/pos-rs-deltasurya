@extends('layouts.app')
@section('title', 'Tambah Voucher')
@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="card-header">
            <h3 class="card-title">Tambah Voucher Diskon</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('voucher.store') }}" method="POST">
                @csrf
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Voucher</label>
                        <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Diskon Reliance Jan 2026" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Opsional)</label>
                        <select name="insurance_name" class="form-select form-select-solid" data-control="select2" data-placeholder="Pilih Asuransi">
                            <option value="">Semua Asuransi</option>
                            @foreach($insurances as $insurance)
                                <option value="{{ $insurance['name'] }}" {{ old('insurance_name') == $insurance['name'] ? 'selected' : '' }}>{{ $insurance['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label required">Tipe Diskon</label>
                        <select name="type" class="form-select form-select-solid" required>
                            <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="nominal" {{ old('type') == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Nilai Diskon</label>
                        <input type="number" step="0.01" name="value" class="form-control form-control-solid" value="{{ old('value') }}" placeholder="Contoh: 5 atau 15000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maksimal Diskon (Capping)</label>
                        <input type="number" step="0.01" name="max_discount" class="form-control form-control-solid" value="{{ old('max_discount') }}" placeholder="Kosongkan jika tidak ada limit">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control form-control-solid" value="{{ old('start_date') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="end_date" class="form-control form-control-solid" value="{{ old('end_date') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('voucher.index') }}" class="btn btn-light me-3">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
