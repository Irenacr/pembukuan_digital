@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Tambah Service</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('service.store') }}" method="POST">
                @csrf

                <!-- Nama Service -->
                <div class="mb-3">
                    <label class="form-label">Nama Service</label>
                    <input type="text" name="nama_service" class="form-control"
                           placeholder="Contoh: Servis Laptop">
                </div>

                <!-- Customer -->
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <input type="text" name="customer" class="form-control"
                           placeholder="Masukkan nama customer">
                </div>

                <!-- Harga -->
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control"
                           placeholder="Masukkan harga service">
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control">
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('service.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection