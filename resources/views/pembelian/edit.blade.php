@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Edit Pembelian</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('pembelian.update', $pembelian->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- PILIH BARANG -->
                <div class="mb-3">
                    <label class="form-label">Barang</label>
                    <select name="barang_id" class="form-select" required>
                        @foreach($barangs as $barang)
                            <option value="{{ $barang->id }}" {{ $pembelian->barang_id === $barang->id ? 'selected' : '' }}>
                                {{ $barang->nama_barang }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- JUMLAH -->
                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" value="{{ $pembelian->jumlah }}" required>
                </div>

                <!-- HARGA BELI -->
                <div class="mb-3">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" name="harga_beli" class="form-control" value="{{ $pembelian->harga_beli }}" required>
                </div>

                <!-- TANGGAL -->
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $pembelian->tanggal }}" required>
                </div>

                <!-- BUTTON -->
                <div class="d-flex justify-content-between">
                    <a href="/pembelian" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection
