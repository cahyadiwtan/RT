<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['resident.house']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $users = $query->orderBy('role', 'desc')->orderBy('username', 'asc')->paginate(15)->withQueryString();

        return view('pengurus.users.index', compact('users'));
    }

    public function create()
    {
        $residents = Resident::with('house')
            ->where('status_warga', 'aktif')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pengurus.users.create', compact('residents'));
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('pengurus.users.index')
            ->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $residents = Resident::with('house')
            ->where('status_warga', 'aktif')
            ->orderBy('nama_lengkap', 'asc')
            ->get();

        return view('pengurus.users.edit', compact('user', 'residents'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($user->id === auth()->id()) {
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        $user->update($data);

        return redirect()->route('pengurus.users.index')
            ->with('success', 'Akun pengguna berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun {$user->username} berhasil {$statusText}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('pengurus.users.index')
            ->with('success', 'Akun pengguna berhasil dihapus.');
    }
}