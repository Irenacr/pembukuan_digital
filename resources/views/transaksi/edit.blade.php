@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Edit Transaksi</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('transaksi.update', $transaksi->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('transaksi._form', ['submitLabel' => 'Update', 'transaksi' => $transaksi])
            </form>

        </div>
    </div>

</div>

@endsection
