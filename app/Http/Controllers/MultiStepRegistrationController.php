<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MultiStepRegistrationController extends Controller
{
    /**
     * Display step one registration form
     */
    public function showStepOne()
    {
        return view('auth.register-step-one');
    }

    /**
     * Store step one data and generate session token
     */
    public function storeStepOne(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:customer,photographer',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
        ]);

        // Generate 32-byte random session token
        $sessionToken = Str::random(32);

        // Store session data in cache with 15-minute TTL (900 seconds)
        Cache::put("registration:$sessionToken", [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'expires_at' => now()->addMinutes(15),
        ], 900);

        return response()->json([
            'success' => true,
            'session_token' => $sessionToken,
            'message' => 'Silakan lanjutkan dengan scan wajah untuk menyelesaikan registrasi',
        ]);
    }

    /**
     * Display step two face scan page
     */
    public function showStepTwo(Request $request)
    {
        $sessionToken = $request->query('token');

        if (!$sessionToken) {
            return redirect()->route('register.step-one')
                ->withErrors(['session' => 'Token sesi tidak ditemukan. Silakan mulai registrasi dari awal.']);
        }

        // Verify session token exists and not expired
        $sessionData = Cache::get("registration:$sessionToken");

        if (!$sessionData || now()->gt($sessionData['expires_at'])) {
            return redirect()->route('register.step-one')
                ->withErrors(['session' => 'Sesi telah berakhir. Silakan mulai registrasi dari awal.']);
        }

        return view('auth.register-step-two', compact('sessionToken'));
    }

    /**
     * Complete registration with face scan
     */
    public function storeStepTwo(Request $request)
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'face_embedding' => 'required|array|size:128',
            'face_embedding.*' => 'numeric',
        ], [
            'session_token.required' => 'Token sesi tidak ditemukan.',
            'face_embedding.required' => 'Data wajah tidak ditemukan.',
            'face_embedding.size' => 'Data wajah tidak valid (harus 128 dimensi).',
            'face_embedding.*.numeric' => 'Data wajah tidak valid.',
        ]);

        // Retrieve session data from cache
        $sessionData = Cache::get("registration:{$validated['session_token']}");

        // Check if session exists and not expired
        if (!$sessionData || now()->gt($sessionData['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi telah berakhir. Silakan mulai registrasi dari awal.',
            ], 419);
        }

        try {
            // Use database transaction for atomicity
            DB::transaction(function () use ($sessionData, $validated) {
                // Create User record
                $user = User::create([
                    'name' => $sessionData['name'],
                    'email' => $sessionData['email'],
                    'password' => $sessionData['password'],
                    'role' => $sessionData['role'],
                ]);

                // Encrypt face embedding using Laravel's Crypt facade
                $encryptedEmbedding = Crypt::encryptString(json_encode($validated['face_embedding']));

                // Create UserFaceEmbedding record
                $userFaceEmbedding = UserFaceEmbedding::create([
                    'user_id' => $user->id,
                    'embedding_vector' => $encryptedEmbedding,
                ]);

                // Update User with face_embedding_id
                $user->update(['face_embedding_id' => $userFaceEmbedding->id]);

                // Authenticate user (auto-login)
                Auth::login($user);
            });

            // Invalidate session token (delete from cache)
            Cache::forget("registration:{$validated['session_token']}");

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil! Anda akan diarahkan ke dashboard.',
                'redirect' => route('dashboard'),
            ]);

        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.',
            ], 500);
        }
    }
}
