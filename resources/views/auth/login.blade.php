<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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

            <h3 class="text-center mb-4 animate__animated animate__fadeInDown">Login</h3>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <!-- 🔥 NAMA (BUKAN EMAIL) -->
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Nama" required>
                </div>

                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <button class="btn btn-primary w-100">Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="/register">Belum punya akun? Register dulu</a>
            </div>

        </div>
    </div>

</div>

</body>
</html>