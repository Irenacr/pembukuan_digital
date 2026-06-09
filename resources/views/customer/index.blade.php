@extends('layout')

@section('content')

<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Data Customer</h2>

        <a href="{{ route('customer.create') }}" class="btn btn-primary">
            + Tambah Customer
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

                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Perusahaan</th>
                        <th>Alamat</th>
                        <th>Kontak</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($customers as $c)
                    <tr>

                        <!-- ID -->
                        <td class="text-center">{{ $c->id }}</td>

                        <!-- NAMA -->
                        <td>{{ $c->nama }}</td>

                        <!-- PERUSAHAAN -->
                        <td>{{ $c->perusahaan }}</td>

                        <!-- ALAMAT -->
                        <td>{{ $c->alamat }}</td>

                        <!-- KONTAK -->
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $c->kontak }}
                            </span>
                        </td>

                        <!-- AKSI -->
                        <td class="text-center">

                            <!-- EDIT -->
                            <a href="{{ route('customer.edit', $c->id) }}"
                               class="btn btn-sm btn-warning">
                               ✏️ Edit
                            </a>

                            <!-- HAPUS -->
                            <form action="{{ route('customer.destroy', $c->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Yakin ingin menghapus customer ini?');">

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
                            Belum ada data customer
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection