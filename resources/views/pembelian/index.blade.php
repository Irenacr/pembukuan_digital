@extends('layout')

@section('content')

<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="bi bi-bag-down me-2"></i> Data Pembelian
        </h2>

        <a href="{{ route('pembelian.create') }}" class="btn btn-primary">
            + Tambah Pembelian
        </a>
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

                <!-- HEADER -->
                <thead class="table-dark text-center">
                    <tr>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody>

                @forelse($pembelians as $p)
                    <tr>

                        <!-- TANGGAL -->
                        <td class="text-center">
                            {{ $p->tanggal 
                                ? \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') 
                                : '-' 
                            }}
                        </td>

                        <!-- BARANG -->
                        <td>
                            {{ $p->barang->nama_barang ?? '-' }}
                        </td>

                        <!-- JUMLAH -->
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                {{ $p->jumlah }}
                            </span>
                        </td>

                        <!-- HARGA -->
                        <td>
                            Rp {{ number_format($p->harga_beli ?? $p->harga,0,',','.') }}
                        </td>

                        <!-- TOTAL -->
                        <td>
                            <strong class="text-danger">
                                Rp {{ number_format($p->total,0,',','.') }}
                            </strong>
                        </td>

                        <!-- AKSI -->
                        <td class="text-center">

                            <!-- EDIT -->
                            <a href="{{ route('pembelian.edit', $p->id) }}"
                               class="btn btn-sm btn-warning">
                               ✏️ Edit
                            </a>

                            <!-- HAPUS -->
                            <form action="{{ route('pembelian.destroy', $p->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Yakin ingin menghapus pembelian ini?');">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger">
                                    🗑️ Hapus
                                </button>
                            </form>

                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Belum ada pembelian
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection