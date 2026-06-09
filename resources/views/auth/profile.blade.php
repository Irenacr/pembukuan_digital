@extends('layout')

@section('content')

<div class="container">

    <h2 class="mb-4 fw-bold">Profile {{ ucfirst(Auth::user()->role) }}</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="text-center mb-4">
                <img src="https://i.pravatar.cc/100?name={{ Auth::user()->name }}" class="rounded-circle mb-2">
                <h5>{{ Auth::user()->name }}</h5>
                <small class="text-muted">{{ Auth::user()->email }}</small>
            </div>

            <hr>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Nama</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->email }}" readonly>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst(Auth::user()->role) }}" readonly>
                </div>

            </div>

            <div class="text-end">
                <a href="/ganti-password" class="btn btn-warning">
                    Ganti Password
                </a>
            </div>

        </div>
    </div>

</div>

@endsection