<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Auth\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', RegisterController::class);
Route::post('/login', LoginController::class);
Route::post('/logout', LogoutController::class)->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

Route::middleware('auth:sanctum')->group(function () {
    // Read all users
    Route::get('/users', function () {
        return User::all();
    });

    // Create user
    Route::post('/users', function (Request $request) {
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
    });

    // Update user
    Route::put('/users/{user}', function (Request $request, User $user) {
        $request->validate([
            'name' => 'string',
            'email' => 'string|email|unique:users,email,'.$user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return response()->json($user);
    });

    // Delete user
    Route::delete('/users/{user}', function (User $user) {
        $user->delete();
        return response()->json(null, 204);
    });
});

// Route::post('/register', function (Request $request) {
//     $request->validate([
//         'name' => 'required|string',
//         'email' => 'required|string|email|unique:users',
//         'password' => 'required|string|min:6',
//     ]);

//     $user = User::create([
//         'name' => $request->name,
//         'email' => $request->email,
//         'password' => Hash::make($request->password),
//     ]);

//     return response()->json($user, 201);
// });

// Route::post('/login', function (Request $request) {
//     $request->validate([
//         'email' => 'required|string|email',
//         'password' => 'required|string',
//     ]);

//     $user = User::where('email', $request->email)->first();

//     if (!$user || !Hash::check($request->password, $user->password)) {
//         return response()->json(['message' => 'Unauthorized'], 401);
//     }

//     return $user->createToken('YourAppName')->plainTextToken;
// });

// Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
//     $request->user()->tokens()->delete();
//     return response()->json(['message' => 'Logged out']);
// });