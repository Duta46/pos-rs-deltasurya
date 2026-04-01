@extends('layouts.app')
@section('title', 'Edit Voucher')
@section('content')
    <div class="card card-docs flex-row-fluid mb-2">
        <div class="card-header">
            <h3 class="card-title">Edit Voucher Diskon</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('voucher.update', $voucher->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-5">
                    <div class="col-md-6">
                        <label class="form-label required">Nama Voucher</label>
                        <input type="text" name="name" class="form-control form-control-solid" value="{{ $voucher->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Asuransi (Opsional)</label>
                        <select name="insurance_name" class="form-select form-select-solid" data-control="select2">
                            <option value="">Semua Asuransi</option>
                            @foreach($insurances as $insurance)
                                <option value="{{ $insurance['name'] }}" {{ $voucher->insurance_name == $insurance['name'] ? 'selected' : '' }}>{{ $insurance['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label required">Tipe Diskon</label>
                        <select name="type" class="form-select form-select-solid" required>
                            <option value="percent" {{ $voucher->type == 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                            <option value="nominal" {{ $voucher->type == 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Nilai Diskon</label>
                        <input type="number" step="0.01" name="value" class="form-control form-control-solid" value="{{ $voucher->value }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maksimal Diskon (Capping)</label>
                        <input type="number" step="0.01" name="max_discount" class="form-control form-control-solid" value="{{ $voucher->max_discount }}">
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control form-control-solid" value="{{ $voucher->start_date ? $voucher->start_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Berakhir</label>
                        <input type="date" name="end_date" class="form-control form-control-solid" value="{{ $voucher->end_date ? $voucher->end_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Status</label>
                        <select name="is_active" class="form-select form-select-solid">
                            <option value="1" {{ $voucher->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !$voucher->is_active ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('voucher.index') }}" class="btn btn-light me-3">Batal</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
