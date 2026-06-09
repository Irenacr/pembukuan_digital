@extends('layout')

@section('content')

<div class="container mt-4">

<!-- WELCOME -->
<div class="alert alert-light border-start border-primary border-4 shadow-sm animate__animated animate__fadeInDown">
    <h5 class="mb-1 text-primary">Halo, {{ Auth::user()->name }}!</h5>
    <small>Selamat datang di dashboard {{ ucfirst(Auth::user()->role) }}.</small>
</div>

<h3 class="mb-4 animate__animated animate__fadeIn">Dashboard</h3>

<!-- ===== RINGKASAN KEUANGAN ===== -->
<div class="row mt-4">

    <!-- UANG MASUK -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-lg animate__animated animate__fadeInUp" style="border-radius:15px; animation-delay: 0.2s;">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center animate__animated animate__bounceIn" style="width:60px;height:60px; animation-delay: 0.5s;">
                    <i class="bi bi-arrow-down fs-4"></i>
                </div>
                <div>
                    <small class="text-muted">Uang Masuk</small>
                    <h4 class="mb-0 fw-bold text-success">
                        Rp {{ number_format($uang_masuk,0,',','.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- UANG KELUAR -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-lg animate__animated animate__fadeInUp" style="border-radius:15px; animation-delay: 0.4s;">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center animate__animated animate__bounceIn" style="width:60px;height:60px; animation-delay: 0.7s;">
                    <i class="bi bi-arrow-up fs-4"></i>
                </div>
                <div>
                    <small class="text-muted">Uang Keluar</small>
                    <h4 class="mb-0 fw-bold text-danger">
                        Rp {{ number_format($uang_keluar,0,',','.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- LABA BERSIH -->
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-lg" style="border-radius:15px;">
            <div class="card-body d-flex align-items-center">
                <div class="me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
                <div>
                    <small class="text-muted">Laba Bersih</small>
                    <h4 class="mb-0 fw-bold text-primary">
                        Rp {{ number_format($laba_bersih,0,',','.') }}
                    </h4>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===== CARD FITUR ===== -->
<div class="row">

    <!-- Total Penjualan -->
    <div class="col-md-3 mb-3">
        <a href="{{ route('transaksi.index') }}" style="text-decoration:none;">
            <div class="card text-white bg-primary shadow"
                 style="cursor:pointer; transition:0.3s;"
                 onmouseover="this.style.transform='scale(1.05)'"
                 onmouseout="this.style.transform='scale(1)'">

                <div class="card-body text-center">
                    <i class="bi bi-graph-up fs-2"></i>
                    <h6 class="mt-2">Total Penjualan</h6>
                    <h4>Rp {{ $total_penjualan }}</h4>
                </div>

            </div>
        </a>
    </div>

    <!-- Jumlah Transaksi -->
    <div class="col-md-3 mb-3">
        <a href="{{ route('transaksi.index') }}" style="text-decoration:none;">
            <div class="card text-white bg-warning shadow"
                 style="cursor:pointer; transition:0.3s;"
                 onmouseover="this.style.transform='scale(1.05)'"
                 onmouseout="this.style.transform='scale(1)'">

                <div class="card-body text-center">
                    <i class="bi bi-cart fs-2"></i>
                    <h6 class="mt-2">Jumlah Transaksi</h6>
                    <h4>{{ $jumlah_transaksi }}</h4>
                </div>

            </div>
        </a>
    </div>

  <!-- 🔥 TOTAL SERVICE FINAL -->
<div class="col-md-3 mb-3">
    <a href="{{ route('service.index') }}" style="text-decoration:none;">
        <div class="card text-white bg-success shadow"
             style="cursor:pointer; transition:0.3s;"
             onmouseover="this.style.transform='scale(1.05)'"
             onmouseout="this.style.transform='scale(1)'">

            <div class="card-body text-center">
                <i class="bi bi-tools fs-2"></i>
                <h6 class="mt-2">Total Service</h6>
                <h4>Rp {{ number_format($total_service ?? 0,0,',','.') }}</h4>
            </div>

        </div>
    </a>
</div>

    <!-- Stok Rendah -->
    <div class="col-md-3 mb-3">
        <a href="{{ route('barang.index') }}" style="text-decoration:none;">
            <div class="card text-white bg-danger shadow"
                 style="cursor:pointer; transition:0.3s;"
                 onmouseover="this.style.transform='scale(1.05)'"
                 onmouseout="this.style.transform='scale(1)'">

                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-2"></i>
                    <h6 class="mt-2">Stok Rendah</h6>
                    <h4>{{ count($stok_rendah) }}</h4>
                    @if(count($stok_rendah) > 0)
                        <small class="text-light d-block mt-1">
                            {{ $stok_rendah->pluck('nama_barang')->take(2)->join(', ') }}{{ count($stok_rendah) > 2 ? ' dan ' . (count($stok_rendah) - 2) . ' lainnya' : '' }}
                        </small>
                    @else
                        <small class="text-light d-block mt-1">Tidak ada stok rendah</small>
                    @endif
                </div>

            </div>
        </a>
    </div>

</div>
   <!-- ===== GRAFIK PENJUALAN ===== -->
<div class="card shadow-sm mt-4">
    <div class="card-body">
        <h5 class="fw-bold">Grafik Penjualan Tahun 2026</h5>
        <canvas id="chartPenjualan"></canvas>
    </div>
</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById('chartPenjualan');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // 🔥 FIX: pakai JSON string
    const labels = JSON.parse('@json($labels)');
    const data = JSON.parse('@json($data)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Penjualan',
                data: data,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5
            }]
        }
    });

});
</script>
@endsection