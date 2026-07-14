<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('overview');
        }
        return view('auth.login');
    }

    /**
     * Handle AJAX login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'redirect' => route('overview')
            ]);
        }

        return response()->json([
            'success' => false,
            'errors' => ['These credentials do not match our records.']
        ], 401);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully.');
    }
}
