<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    // Menampilkan halaman pembayaran
    public function show(Transaction $transaction)
    {
        // Validasi hanya buyer yang bisa akses
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        return view('payment.index', compact('transaction'));
    }

    // Proses dummy payment (untuk development)
    public function process(Request $request, Transaction $transaction)
    {
        // Validasi
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403);
        }

        // Simulasi pembayaran berhasil
        // Dalam production, ini akan integrasi dengan midtrans/stripe
        $transaction->update(['status' => 'completed']);

        // Ambil transaction items untuk ditampilkan
        $transactionItems = $transaction->items()->with('photo.album')->get();

        return view('payment.success', [
            'transaction' => $transaction,
            'transactionItems' => $transactionItems,
        ]);
    }
}
