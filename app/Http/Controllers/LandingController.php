<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::where('is_public', true)
            ->where(function($q){ $q->whereNull('start_date')->orWhere('start_date', '>=', now()); })
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        return view('landing', compact('events'));
    }
}
