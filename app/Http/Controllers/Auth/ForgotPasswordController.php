<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'nik' => ['required', 'string', 'digits:16'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('username', $request->username)
            ->whereHas('resident', function ($q) use ($request) {
                $q->where('nik', $request->nik)->where('is_verified', true);
            })
            ->first();

        if (! $user) {
            return back()->withErrors([
                'username' => 'Kombinasi Username dan NIK tidak ditemukan atau warga belum terverifikasi.',
            ])->onlyInput('username');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Password berhasil diperbarui! Silakan login dengan password baru.');
    }
}
