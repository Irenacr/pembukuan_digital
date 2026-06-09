@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Tambah Pembelian</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="/pembelian" method="POST">
                @csrf

                <!-- PILIH BARANG -->
                <div class="mb-3">
                    <label class="form-label">Barang</label>
                    <select name="barang_id" class="form-select">
                        @foreach($barangs as $barang)
                            <option value="{{ $barang->id }}">
                                {{ $barang->nama_barang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- JUMLAH -->
                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control">
                </div>

                <!-- HARGA BELI -->
                <div class="mb-3">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" name="harga_beli" class="form-control">
                </div>

                <!-- TANGGAL -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control">
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="/pembelian" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection