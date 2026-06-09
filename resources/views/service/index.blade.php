@extends('layout')

@section('content')

<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Data Service</h2>

        <a href="{{ route('service.create') }}" class="btn btn-primary">
            + Tambah Service
        </a>
    </div>

    <!-- NOTIFIKASI -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- CARD -->
    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-hover table-bordered align-middle">

                <!-- HEADER -->
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Service</th>
                        <th>Customer</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody>

                @forelse($services as $s)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $s->nama_service }}</td>
                        <td>{{ $s->customer }}</td>
                        <td>Rp {{ number_format($s->harga,0,',','.') }}</td>

                        <!-- STATUS -->
                        <td class="text-center">
                            @if($s->status == 'selesai')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-warning text-dark">Proses</span>
                            @endif
                        </td>

                        <!-- TANGGAL -->
                        <td class="text-center">
                            {{ \Carbon\Carbon::parse($s->tanggal)->format('d-m-Y') }}
                        </td>

                        <!-- AKSI -->
                        <td class="text-center">

                            <!-- EDIT -->
                            <a href="{{ route('service.edit', $s->id) }}"
                               class="btn btn-sm btn-warning me-1">
                                ✏️ Edit
                            </a>

                            <!-- HAPUS -->
                            <form action="{{ route('service.destroy', $s->id) }}"
                                  method="POST"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Yakin ingin menghapus data ini?');">

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
                        <td colspan="7" class="text-center text-muted">
                            Belum ada data service
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection