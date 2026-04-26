<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    // Menampilkan purchase history (halaman utama)
    public function index()
    {
        $transactions = Transaction::where('buyer_id', Auth::id())
            ->with('items.photo.album.photographer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('purchase.history', compact('transactions'));
    }

    // Alias untuk history (nama yang sama)
    public function history()
    {
        return $this->index();
    }

    // Menampilkan detail purchase (legacy, untuk compatibility)
    public function show(Transaction $transaction)
    {
        // Validasi hanya buyer yang bisa akses
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $transaction->load('items.photo.album');

        return view('purchase.history', ['transactions' => collect([$transaction])]);
    }

    // Download foto original (tanpa watermark) - hanya untuk yang sudah beli
    public function download($transactionItemId)
    {
        // Cari TransactionItem
        $transactionItem = \App\Models\TransactionItem::findOrFail($transactionItemId);
        $transaction = $transactionItem->transaction;

        // Validasi buyer
        if ($transaction->buyer_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Validasi transaksi sudah dibayar
        if (!in_array($transaction->status, ['paid', 'completed'])) {
            abort(403, 'Silakan selesaikan pembayaran terlebih dahulu.');
        }

        $photo = $transactionItem->photo;

        // Cek file ada
        if (!Storage::disk('public')->exists($photo->original_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Download file
        return Storage::disk('public')->download(
            $photo->original_path,
            'fotlist-' . $photo->album->title . '-' . now()->format('YmdHis') . '.' . pathinfo($photo->original_path, PATHINFO_EXTENSION)
        );
    }
}
