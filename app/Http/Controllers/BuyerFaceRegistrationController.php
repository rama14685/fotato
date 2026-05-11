<?php

namespace App\Http\Controllers;

use App\Models\UserFace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BuyerFaceRegistrationController extends Controller
{
    /**
     * Show the face registration page (webcam capture).
     * If the user already has a face registered, allow re-registration.
     */
    public function index()
    {
        $user = Auth::user();

        // Only buyers/customers need face registration
        if (!in_array($user->role, ['buyer', 'customer'])) {
            return redirect()->route('dashboard');
        }

        $hasFace = $user->userFace !== null;

        return view('buyer.register-face', compact('hasFace'));
    }

    /**
     * Save the face descriptor captured from the webcam via AJAX.
     *
     * Expects JSON body: { face_descriptor: [128 floats] }
     */
    public function store(Request $request)
    {
        $request->validate([
            'face_descriptor'   => 'required|array|size:128',
            'face_descriptor.*' => 'numeric',
        ], [
            'face_descriptor.required' => 'Data wajah tidak ditemukan.',
            'face_descriptor.size'     => 'Data wajah tidak valid (harus 128 dimensi).',
            'face_descriptor.*.numeric'=> 'Data wajah mengandung nilai tidak valid.',
        ]);

        try {
            $user = Auth::user();

            // Upsert: update if exists, insert if not
            UserFace::updateOrCreate(
                ['user_id' => $user->id],
                ['face_descriptor' => $request->face_descriptor]
            );

            Log::info('Buyer face registered', ['user_id' => $user->id]);

            return response()->json([
                'success'  => true,
                'message'  => 'Wajah berhasil didaftarkan!',
                'redirect' => route('buyer.dashboard'),
            ]);

        } catch (\Exception $e) {
            Log::error('Face registration failed', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data wajah. Silakan coba lagi.',
            ], 500);
        }
    }
}
