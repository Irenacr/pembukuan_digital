@extends('layout')

@section('content')

<div class="container">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Data Transaksi</h2>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('transaksi.scan') }}" class="btn btn-secondary">
                🔍 Scan Nota
            </a>
            <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
                + Tambah Transaksi
            </a>
        </div>
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
                        <th>Customer</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Total Harga Barang</th>
                        <th>Total</th>
                        <th width="90">Status</th>
                        <th>Lokasi</th>
                        <th>Nama Penerima</th>
                        <th width="100">Nota Scan</th>
                        <th width="140">OCR Teks</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($transaksis as $t)
                    <tr>

                        <!-- TANGGAL -->
                        <td>
                            {{ $t->tanggal 
                                ? \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y') 
                                : '-' 
                            }}
                        </td>

                        <!-- ID -->
                        <td class="text-center">{{ $t->id }}</td>

                        <!-- CUSTOMER -->
                        <td>{{ $t->customer->nama ?? '-' }}</td>

                        <!-- NAMA BARANG -->
                        <td>
                            @foreach($t->detailTransaksis as $detail)
                                {{ $detail->barang->nama_barang ?? '-' }}<br>
                            @endforeach
                        </td>

                        <!-- KATEGORI -->
                        <td>
                            @foreach($t->detailTransaksis as $detail)
                                {{ $detail->barang->kategori ?? '-' }}<br>
                            @endforeach
                        </td>

                        <!-- JUMLAH -->
                        <td class="text-center">
                            @foreach($t->detailTransaksis as $detail)
                                {{ $detail->qty }}<br>
                            @endforeach
                        </td>

                        <!-- TOTAL HARGA BARANG -->
                        <td>Rp {{ number_format($t->detailTransaksis->sum('subtotal'), 0, ',', '.') }}</td>

                        <!-- TOTAL -->
                        <td>Rp {{ number_format($t->total, 0, ',', '.') }}</td>

                        <!-- STATUS -->
                        <td class="text-center">
                            @if($t->status_pembayaran == 'lunas')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($t->status_pembayaran == 'DP')
                                <span class="badge bg-warning text-dark">DP</span>
                            @else
                                <span class="badge bg-danger">Belum Bayar</span>
                            @endif
                        </td>

                        <!-- LOKASI -->
                        <td>{{ $t->lokasi_pengiriman ?? '-' }}</td>

                        <!-- NAMA PENERIMA -->
                        <td>{{ $t->nama_penerima ?? '-' }}</td>

                        <!-- NOTA SCAN -->
                        <td class="text-center">
                            @if($t->nota_scan)
                                <a href="{{ asset('storage/' . $t->nota_scan) }}" target="_blank" class="btn btn-sm btn-info" title="Download Nota">
                                    📄 Lihat
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <!-- OCR TEXT -->
                        <td>
                            @if($t->nota_text)
                                <div style="max-height: 90px; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($t->nota_text, 120) }}
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td class="text-center">

                            <!-- EDIT -->
                            <a href="{{ route('transaksi.edit', $t->id) }}"
                               class="btn btn-sm btn-warning">
                               ✏️ Edit
                            </a>

                            <!-- HAPUS -->
                            <form action="{{ route('transaksi.destroy', $t->id) }}"
                                  method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">

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
                        <td colspan="14" class="text-center text-muted">
                            Belum ada transaksi
                        </td>
                    </tr>
                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

@endsection