@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Tambah Barang</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('barang.store') }}" method="POST">
                @csrf

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="{{ old('tanggal', date('Y-m-d')) }}">
                </div>

                <!-- Nama Barang -->
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control"
                           value="{{ old('nama_barang') }}"
                           placeholder="Masukkan nama barang">
                </div>

                <!-- Kategori -->
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="" {{ old('kategori') == '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                        <option value="barang_baru" {{ old('kategori') == 'barang_baru' ? 'selected' : '' }}>
                            Barang Baru
                        </option>
                        <option value="barang_bekas" {{ old('kategori') == 'barang_bekas' ? 'selected' : '' }}>
                            Barang Bekas
                        </option>
                    </select>
                </div>

                <!-- Stok -->
                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control"
                           value="{{ old('stok') }}"
                           placeholder="Masukkan jumlah stok">
                </div>

                <!-- Harga -->
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control"
                           value="{{ old('harga') }}"
                           placeholder="Masukkan harga">
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection