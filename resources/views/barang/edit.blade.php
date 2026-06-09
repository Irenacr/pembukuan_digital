@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Edit Barang</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('barang.update', $barang->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Tanggal -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $barang->tanggal }}">
                </div>

                <!-- Nama Barang -->
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama_barang" class="form-control" placeholder="Masukkan nama barang" value="{{ $barang->nama_barang }}" required>
                </div>

                <!-- Kategori -->
                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select" required>
                        <option value="" {{ $barang->kategori == null ? 'selected' : '' }}>-- Pilih Kategori --</option>
                        <option value="barang_baru" {{ $barang->kategori === 'barang_baru' ? 'selected' : '' }}>Barang Baru</option>
                        <option value="barang_bekas" {{ $barang->kategori === 'barang_bekas' ? 'selected' : '' }}>Barang Bekas</option>
                    </select>
                </div>

                <!-- Stok -->
                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" placeholder="Masukkan jumlah stok" value="{{ $barang->stok }}" required>
                </div>

                <!-- Harga -->
                <div class="mb-3">
                    <label class="form-label">Harga</label>
                    <input type="number" name="harga" class="form-control" placeholder="Masukkan harga" value="{{ $barang->harga }}" required>
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="/barang" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
