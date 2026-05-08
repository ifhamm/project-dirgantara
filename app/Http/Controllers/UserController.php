<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'superadmin');

        // Logic Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Logic Filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('sort_by') && in_array($request->sort_by, ['nik', 'name'])) {
            $sortOrder = $request->get('sort_order', 'asc');
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
            $query->orderBy($request->sort_by, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.account', compact('users'));
    }

    public function store(UserRequest $request)
    {
        $data = [
            'name' => $request->name,
            'nik'  => $request->nik,
            'role' => $request->role,
            'email' => $request->role === 'admin' ? $request->email : null,
        ];

        // Jika password diisi (untuk admin), atau default password pakai NIK untuk yang lain
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            $data['password'] = bcrypt($request->nik ?? 'password123');
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UserRequest $request, User $user)
    {
        $data = [
            'name' => $request->name,
            'nik'  => $request->nik,
            'role' => $request->role,
            'email' => $request->role === 'admin' ? $request->email : $user->email,
        ];

        // Update password hanya jika diisi
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
