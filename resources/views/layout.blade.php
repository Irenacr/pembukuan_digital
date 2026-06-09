<!DOCTYPE html>
<html>
<head>
    <title>Pembukuan</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        body{
            margin:0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .sidebar{
            width:240px;
            height:100vh;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color:white;
            position:fixed;
            padding:20px;
            box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
        }

        .sidebar h4{
            margin-bottom:30px;
            font-size: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            margin:12px 0;
            padding:12px 15px;
            border-radius:12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .sidebar a:hover::before {
            left: 100%;
        }

        .sidebar a:hover{
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .content{
            margin-left:260px;
            padding:30px;
            background: rgba(255,255,255,0.95);
            min-height: 100vh;
            border-radius: 20px 0 0 20px;
            box-shadow: -5px 0 20px rgba(0,0,0,0.1);
        }

        .card{
            border-radius:15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.15);
        }

        .btn {
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

</head>

<body class="bg-light">

@auth
    <!-- SIDEBAR -->
    <div class="sidebar animate__animated animate__slideInLeft">
        <h4 class="fw-bold animate-fade-in">💼 UD Cahaya Sinar</h4>

        <a href="{{ route('dashboard') }}" class="animate-fade-in">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        <a href="{{ route('barang.index') }}" class="animate-fade-in">
            <i class="bi bi-box-seam me-2"></i> Barang
        </a>

        <a href="{{ route('customer.index') }}" class="animate-fade-in">
            <i class="bi bi-people me-2"></i> Customer
        </a>

        <a href="{{ route('transaksi.index') }}" class="animate-fade-in">
            <i class="bi bi-cart-check me-2"></i> Transaksi
        </a>

        {{-- 🔥 KHUSUS ADMIN --}}
        @if(Auth::user()->role === 'admin')
            <a href="{{ route('pembelian.index') }}" class="animate-fade-in">
                <i class="bi bi-box-arrow-in-down me-2"></i> Pembelian
            </a>
        @endif

        <a href="{{ route('service.index') }}" class="animate-fade-in">
            <i class="bi bi-tools me-2"></i> Service
        </a>
    </div>
@endauth


<!-- CONTENT -->
<div class="content animate__animated animate__fadeInUp">

    <!-- NAVBAR -->
    <nav class="d-flex justify-content-end align-items-center mb-4">

        @auth
        <div class="dropdown">

            <button class="btn d-flex align-items-center" data-bs-toggle="dropdown">
                <img src="https://i.pravatar.cc/40" class="rounded-circle me-2">

                <div class="text-start">
                    <strong>{{ Auth::user()->name }}</strong><br>
                    <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                </div>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">

                <li class="px-3 py-2">
                    <strong>{{ Auth::user()->name }}</strong><br>
                    <small class="text-muted">{{ Auth::user()->email }}</small>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item" href="{{ route('profile') }}">Profile</a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('password') }}">Ganti Password</a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <!-- LOGOUT -->
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger" style="border:none;background:none;">
                            Logout
                        </button>
                    </form>
                </li>

            </ul>

        </div>
        @endauth

    </nav>

    <!-- ALERT -->
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

    <!-- CONTENT -->
    @yield('content')

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>