<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =========================
    // VIEW LOGIN
    // =========================
    public function showLogin()
    {
        return view('auth.login');
    }

    // =========================
    // VIEW REGISTER
    // =========================
    public function showRegister()
    {
        return view('auth.register');
    }

    // =========================
    // REGISTER
    // =========================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|confirmed',
        ]);

        User::create([

            'name' => $request->name,

            // EMAIL AUTO UNIK
            'email' => uniqid() . '@dummy.com',

            // PASSWORD
            'password' => Hash::make($request->password),

            // DEFAULT ROLE
            'role' => 'karyawan'

        ]);

        return redirect('/login')
            ->with('success', 'Register berhasil!');
    }

    // =========================
    // LOGIN
    // =========================
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'name' => $request->name,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            // 🔥 SEMUA KARYAWAN REDIRECT KE /dashboard (TIDAK ADA PEMISAHAN)
            return redirect('/dashboard');
        }

        return back()->with('error', 'Nama atau password salah');
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }
}