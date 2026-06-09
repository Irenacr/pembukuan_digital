@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Edit Service</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('service.update', $service->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Nama Service -->
                <div class="mb-3">
                    <label class="form-label">Nama Service</label>
                    <input type="text" name="nama_service" class="form-control"
                           value="{{ old('nama_service', $service->nama_service) }}">
                </div>

                <!-- Customer -->
                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <input type="text" name="customer" class="form-control"
                           value="{{ old('customer', $service->customer) }}">
                </div>

                <!-- Harga -->
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control"
                           value="{{ old('harga', $service->harga) }}">
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">

                        <option value="proses"
                            {{ old('status', $service->status) == 'proses' ? 'selected' : '' }}>
                            Proses
                        </option>

                        <option value="selesai"
                            {{ old('status', $service->status) == 'selesai' ? 'selected' : '' }}>
                            Selesai
                        </option>

                    </select>
                </div>

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="{{ old('tanggal', $service->tanggal) }}">
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('service.index') }}" class="btn btn-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection