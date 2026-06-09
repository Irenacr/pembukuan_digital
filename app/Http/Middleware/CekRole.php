<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CekRole
{
    /**
     * Handle an incoming request.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        // Cek apakah karyawan sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }

        // Cek apakah role karyawan sesuai
        if (Auth::user()->role !== $role) {
            // 🔥 TAMPILKAN 403 FORBIDDEN JIKA ROLE TIDAK SESUAI
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}