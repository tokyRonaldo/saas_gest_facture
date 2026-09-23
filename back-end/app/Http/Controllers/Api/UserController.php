<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->whereHas('roles', fn ($r) => $r->where('name', $role));
        }

        return $query->with('roles:name')->latest()->paginate(10);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json($user->load('roles:name'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles:name'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $user->syncRoles([$request->role]);

        return response()->json($user->load('roles:name'));
    }

    public function toggleActif(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Tu ne peux pas désactiver ton propre compte.'], 422);
        }

        $user->update(['actif' => !$user->actif]);

        return response()->json($user->load('roles:name'));
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'Tu ne peux pas supprimer ton propre compte.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}