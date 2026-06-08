<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $photoId => $item) {
            $photo = Photo::with(['album', 'album.photographer'])->find($photoId);
            if ($photo) {
                $isPurchased = $this->isPhotoPurchased($photoId);
                $cartItems[] = [
                    'photo' => $photo,
                    'quantity' => $item['quantity'],
                    'is_purchased' => $isPurchased,
                ];
                if (!$isPurchased) {
                    $total += $photo->price * $item['quantity'];
                }
            }
        }

        return view('cart.index', [
            'cartItems' => $cartItems,
            'totalPrice' => $total,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'photo_id' => 'required|exists:photos,id',
        ]);

        $photoId = $request->photo_id;
        
        // Cek apakah foto sudah dibeli
        if ($this->isPhotoPurchased($photoId)) {
            return redirect()->back()->with('error', 'Anda sudah membeli foto ini!');
        }

        $cart = session()->get('cart', []);
        
        if (isset($cart[$photoId])) {
            if ($request->has('buy_now') && $request->buy_now) {
                return redirect()->route('checkout.index');
            }
            return redirect()->back()->with('info', 'Foto sudah ada di keranjang!');
        }

        $cart[$photoId] = [
            'quantity' => 1,
        ];

        session()->put('cart', $cart);

        if ($request->has('buy_now') && $request->buy_now) {
            return redirect()->route('checkout.index');
        }

        return redirect()->back()->with('success', 'Foto ditambahkan ke keranjang!');
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        
        $photoId = $request->photo_id;
        $quantity = $request->quantity;

        if (isset($cart[$photoId])) {
            if ($quantity <= 0) {
                unset($cart[$photoId]);
            } else {
                $cart[$photoId]['quantity'] = $quantity;
            }
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Keranjang diperbarui!');
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        
        unset($cart[$request->photo_id]);

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Foto dihapus dari keranjang!');
    }

    public function clear()
    {
        session()->forget('cart');

        return redirect()->back()->with('success', 'Keranjang dikosongkan!');
    }

    private function isPhotoPurchased($photoId)
    {
        if (!auth()->check()) {
            return false;
        }

        return TransactionItem::whereHas('transaction', function($q) {
            $q->where('buyer_id', auth()->id())
              ->where('status', 'completed');
        })->where('photo_id', $photoId)->exists();
    }
}
