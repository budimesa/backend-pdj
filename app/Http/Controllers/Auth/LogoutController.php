<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle the logout request.
     */
    public function __invoke(Request $request)
    {
        // Mengambil user yang sedang login
        $user = $request->user();
        
        // Menghapus token autentikasi
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
