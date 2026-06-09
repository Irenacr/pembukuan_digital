@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Tambah Customer</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="/customer" method="POST">
                @csrf

                <div class="mb-3">
                     <label>Tanggal</label>
                     <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
                </div>

                <!-- Nama -->
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama customer">
                </div>

                <!-- Perusahaan -->
                <div class="mb-3">
                    <label class="form-label">Perusahaan</label>
                    <input type="text" name="perusahaan" class="form-control" placeholder="Masukkan nama perusahaan">
                </div>

                <!-- Alamat -->
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan alamat"></textarea>
                </div>

                <!-- Kontak -->
                <div class="mb-3">
                    <label class="form-label">Kontak</label>
                    <input type="text" name="kontak" class="form-control" placeholder="Masukkan nomor kontak">
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="/customer" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection