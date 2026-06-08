<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    // Menampilkan halaman checkout
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang kosong!');
        }

        $totalPrice = 0;
        $cartItems = [];
        foreach ($cart as $photoId => $item) {
            $photo = Photo::with('album')->find($photoId);
            if ($photo) {
                $totalPrice += $photo->price * $item['quantity'];
                $cartItems[] = [
                    'photo_id' => $photo->id,
                    'title' => $photo->album->title,
                    'price' => $photo->price,
                    'quantity' => $item['quantity'],
                ];
            }
        }

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'totalPrice' => $totalPrice,
            'user' => Auth::user(),
        ]);
    }

    // Proses checkout
    public function process(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang kosong!');
        }

        // Validasi
        $request->validate([
            'phone' => 'required|string',
            'address' => 'required|string',
        ]);

        $user = Auth::user();
        $totalAmount = 0;
        $firstPhotographerId = null;

        // Hitung total dan ambil photographer_id dari item pertama
        foreach ($cart as $photoId => $item) {
            $photo = Photo::with('album')->find($photoId);
            if ($photo) {
                $totalAmount += $photo->price * $item['quantity'];
                
                if (!$firstPhotographerId) {
                    $firstPhotographerId = $photo->album->photographer_id;
                }
            }
        }

        // Buat transaction dengan photographer_id yang valid
        $transaction = Transaction::create([
            'buyer_id' => $user->id,
            'photographer_id' => $firstPhotographerId,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Buat transaction items
        foreach ($cart as $photoId => $item) {
            $photo = Photo::find($photoId);
            if ($photo) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'photo_id' => $photo->id,
                    'price' => $photo->price,
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        // Clear cart
        session()->forget('cart');

        // Redirect ke payment (untuk sekarang ke dummy payment)
        return redirect()->route('payment.show', $transaction->id)
            ->with('success', 'Pesanan dibuat! Lanjutkan ke pembayaran.');
    }
}
