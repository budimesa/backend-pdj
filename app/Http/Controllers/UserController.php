<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Read all users
    public function index()
    {
        return User::all();
    }

    // Create user
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'string',
            'email' => 'string|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return response()->json($user, 200);
    }

    // Delete user
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }
}
