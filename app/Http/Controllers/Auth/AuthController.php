<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        // Check if the current password is correct
        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['error' => 'Current password is incorrect.'], 403);
        }

        // Update password
        auth()->user()->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password changed successfully.']);
    }

}
