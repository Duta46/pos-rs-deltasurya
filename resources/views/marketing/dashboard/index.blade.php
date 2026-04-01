@extends('layouts.app')
@section('title', 'Dashboard Marketing')
@section('page-title')
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
        <h1 class="page-heading d-flex text-dark fw-bold flex-column justify-content-center my-0">
            Dashboard Marketing
        </h1>
    </div>
@endsection
@section('content')
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Statistik Summary -->
        <div class="col-md-3">
            <div class="card card-flush h-md-100 bg-primary">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['total_visits'] }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Kunjungan (Lunas)</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush h-md-100 bg-success">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Pendapatan</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush h-md-100 bg-warning">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['active_vouchers'] }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Voucher Aktif</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-flush h-md-100 bg-info">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['voucher_usage'] }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Penggunaan Voucher</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Grafik Kunjungan Asuransi -->
        <div class="col-md-6">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Top Asuransi (Kunjungan Pasien)</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Berdasarkan jumlah transaksi lunas</span>
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="chartVisits" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Grafik Pendapatan Asuransi -->
        <div class="col-md-6">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Top Asuransi (Pendapatan Rupiah)</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Berdasarkan total nominal pembayaran</span>
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="chartRevenue" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!-- Tren Kunjungan -->
        <div class="col-md-12">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Tren Pertumbuhan Kunjungan</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Perkembangan dalam 6 bulan terakhir</span>
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="chartTrend" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Chart Visits
    const ctxVisits = document.getElementById('chartVisits').getContext('2d');
    new Chart(ctxVisits, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topInsurancesVisits->pluck('insurance_name')) !!},
            datasets: [{
                label: 'Kunjungan',
                data: {!! json_encode($topInsurancesVisits->pluck('total')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderRadius: 5
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. Chart Revenue
    const ctxRevenue = document.getElementById('chartRevenue').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topInsurancesRevenue->pluck('insurance_name')) !!},
            datasets: [{
                data: {!! json_encode($topInsurancesRevenue->pluck('total')) !!},
                backgroundColor: [
                    '#50cd89', '#7239ea', '#ffc700', '#f1416c', '#009ef7'
                ]
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 3. Chart Trend
    const ctxTrend = document.getElementById('chartTrend').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyTrend->pluck('month')) !!},
            datasets: [{
                label: 'Kunjungan Per Bulan',
                data: {!! json_encode($monthlyTrend->pluck('total')) !!},
                borderColor: '#009ef7',
                backgroundColor: 'rgba(0, 158, 247, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
