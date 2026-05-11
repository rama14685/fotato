<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresBuyerFace
{
    /**
     * Redirect buyers who haven't registered their face yet.
     * Allow users with role 'customer' or 'buyer' through this check.
     * Admin users are not affected.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Only apply to buyer/customer roles
        if ($user && in_array($user->role, ['buyer', 'customer'])) {
            if (!$user->userFace) {
                return redirect()->route('buyer.register-face')
                    ->with('info', 'Silakan daftarkan wajah Anda terlebih dahulu untuk menggunakan fitur ini.');
            }
        }

        return $next($request);
    }
}
