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
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        return view('checkout.index', [
            'cartItems' => $cart,
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
        foreach ($cart as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
            
            if (!$firstPhotographerId) {
                $photo = Photo::find($item['photo_id']);
                if ($photo) {
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
        foreach ($cart as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'photo_id' => $item['photo_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        // Clear cart
        session()->forget('cart');

        // Redirect ke payment (untuk sekarang ke dummy payment)
        return redirect()->route('payment.show', $transaction->id)
            ->with('success', 'Pesanan dibuat! Lanjutkan ke pembayaran.');
    }
}
