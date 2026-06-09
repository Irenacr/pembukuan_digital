@extends('layout')

@section('content')

<div class="container">

<h3>Ganti Password</h3>

<div class="card p-4 mt-3">

    <form>

        <div class="mb-3">
            <label>Password Lama</label>
            <input type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" class="form-control">
        </div>

        <button class="btn btn-primary">Simpan</button>

    </form>

</div>

</div>

@endsection