@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Tambah Transaksi</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('transaksi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('transaksi._form', ['submitLabel' => 'Simpan', 'transaksi' => null])
            </form>

        </div>
    </div>

</div>

@endsection