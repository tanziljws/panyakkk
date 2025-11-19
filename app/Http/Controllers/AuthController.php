<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username' => 'required|string|max:255',
                'password' => 'required|string|max:255',
            ]);

            $remember = (bool) $request->boolean('remember');

            // 1) Coba login dengan email
            if (Auth::guard('web')->attempt(['email' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
                $request->session()->regenerate();

                $user = Auth::user();
                
                // Log activity in background (non-blocking) to prevent timeout
                try {
                    ActivityLog::logUserActivity('user_login', 'User login: ' . $user->name, $user->id, 'User berhasil login ke sistem');
                } catch (\Exception $e) {
                    // Don't break login if activity log fails
                    \Log::warning('Failed to log user activity: ' . $e->getMessage());
                }

                return redirect()->intended('/profil');
            }

            // 2) Jika gagal, coba login dengan nama
            if (Auth::guard('web')->attempt(['name' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
                $request->session()->regenerate();

                $user = Auth::user();
                
                // Log activity in background (non-blocking) to prevent timeout
                try {
                    ActivityLog::logUserActivity('user_login', 'User login: ' . $user->name, $user->id, 'User berhasil login ke sistem');
                } catch (\Exception $e) {
                    // Don't break login if activity log fails
                    \Log::warning('Failed to log user activity: ' . $e->getMessage());
                }

                return redirect()->intended('/profil');
            }

            return back()->withErrors([
                'username' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
            ])->withInput($request->only('username'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->only('username'));
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password']),
            ]);
            
            return back()->withErrors([
                'username' => 'Terjadi kesalahan saat proses login. Silakan coba lagi.',
            ])->withInput($request->only('username'));
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
} 