@extends('layout')

@section('content')

@php
    $isAdmin = Auth::user()->role === 'admin';
@endphp

<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Data Barang</h2>

        @if($isAdmin)
            <a href="{{ route('barang.create') }}" class="btn btn-primary">
                + Tambah Barang
            </a>
        @endif
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- CARD TABLE -->
    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-hover table-bordered align-middle">

                <thead class="table-dark text-center">
                    <tr>
                        <th>Tanggal</th>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        @if($isAdmin)
                            <th width="180">Aksi</th>
                        @endif
                    </tr>
                </thead>

                <tbody>

                @forelse($barangs as $barang)
                    <tr>
                        <td>
                            {{ $barang->tanggal ? \Carbon\Carbon::parse($barang->tanggal)->format('d-m-Y') : '-' }}
                        </td>

                        <td class="text-center">{{ $barang->id }}</td>

                        <td>{{ $barang->nama_barang }}</td>

                        <!-- STOK -->
                        <td class="text-center">
                            <span class="badge {{ $barang->stok < 5 ? 'bg-danger' : 'bg-success' }}">
                                {{ $barang->stok }}
                            </span>
                        </td>

                        <!-- KATEGORI -->
                        <td class="text-center">
                            @php
                                $kat = $barang->kategori ?? '';
                                if ($kat === 'barang_baru') $label = 'Barang Baru';
                                elseif ($kat === 'barang_bekas') $label = 'Barang Bekas';
                                elseif ($kat) $label = ucfirst($kat);
                                else $label = '-';
                            @endphp
                            <span>{{ $label }}</span>
                        </td>

                        <!-- HARGA -->
                        <td>
                            Rp {{ number_format($barang->harga, 0, ',', '.') }}
                        </td>

                        @if($isAdmin)
                            <!-- AKSI -->
                            <td class="text-center">

                                <!-- EDIT -->
                                <a href="{{ route('barang.edit', $barang->id) }}"
                                   class="btn btn-warning btn-sm">
                                   Edit
                                </a>

                                <!-- HAPUS -->
                                <form action="{{ route('barang.destroy', $barang->id) }}"
                                      method="POST"
                                      style="display:inline-block;"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>

                            </td>
                        @endif
                    </tr>

                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center text-muted">
                            Belum ada data barang
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection
