@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Edit Customer</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('customer.update', $customer->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $customer->tanggal }}">
                </div>

                <!-- Nama -->
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama customer" value="{{ $customer->nama }}" required>
                </div>

                <!-- Perusahaan -->
                <div class="mb-3">
                    <label class="form-label">Perusahaan</label>
                    <input type="text" name="perusahaan" class="form-control" placeholder="Masukkan nama perusahaan" value="{{ $customer->perusahaan }}">
                </div>

                <!-- Alamat -->
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" placeholder="Masukkan alamat" rows="3" required>{{ $customer->alamat }}</textarea>
                </div>

                <!-- Kontak -->
                <div class="mb-3">
                    <label class="form-label">Kontak</label>
                    <input type="text" name="kontak" class="form-control" placeholder="Masukkan nomor kontak" value="{{ $customer->kontak }}" required>
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="/customer" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
