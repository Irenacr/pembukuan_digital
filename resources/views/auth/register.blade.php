<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: none;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            transition: transform 0.3s ease;
        }
        .btn-primary:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="bg-light">

<div class="d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow animate__animated animate__zoomIn" style="width:350px;">
        <div class="card-body">

            <h3 class="text-center mb-4 animate__animated animate__fadeInDown">Register</h3>

            {{-- ERROR --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/register">
                @csrf

                <!-- NAMA -->
                <div class="mb-2">
                    <input type="text" name="name" class="form-control" placeholder="Nama" required>
                </div>

                <!-- ROLE -->
                <div class="mb-2">
                    <select name="role" class="form-control" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin">Admin</option>
                        <option value="karyawan">Karyawan</option>
                    </select>
                </div>

                <!-- PASSWORD -->
                <div class="mb-2">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <!-- KONFIRMASI PASSWORD -->
                <div class="mb-3">
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
                </div>

                <button class="btn btn-success w-100">Register</button>
            </form>

            <div class="text-center mt-3">
                <a href="/login">Sudah punya akun? Login</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>