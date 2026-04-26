<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    // Menampilkan cart
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('cart.index', [
            'cartItems' => $cart,
            'totalPrice' => $total,
        ]);
    }

    // Tambah foto ke cart
    public function add(Request $request)
    {
        $cart = session()->get('cart', []);
        
        $photoId = $request->photo_id;
        $quantity = $request->quantity ?? 1;

        // Cek apakah foto sudah ada di cart
        if (isset($cart[$photoId])) {
            $cart[$photoId]['quantity'] += $quantity;
        } else {
            $cart[$photoId] = [
                'photo_id' => $photoId,
                'title' => $request->title,
                'price' => $request->price,
                'photographer' => $request->photographer,
                'image' => $request->image,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Foto ditambahkan ke keranjang!');
    }

    // Update quantity item di cart
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

    // Hapus item dari cart
    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        
        unset($cart[$request->photo_id]);

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Foto dihapus dari keranjang!');
    }

    // Clear cart
    public function clear()
    {
        session()->forget('cart');

        return redirect()->back()->with('success', 'Keranjang dikosongkan!');
    }
}
