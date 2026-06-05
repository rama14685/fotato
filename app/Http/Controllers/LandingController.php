<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        return view('landing');
    }

    public function events(Request $request)
    {
        $events = Event::where('is_public', true)
            ->where(function($q){ $q->whereNull('start_date')->orWhere('start_date', '>=', now()->startOfDay()); })
            ->orderBy('start_date', 'asc')
            ->get();

        return view('events.index', compact('events'));
    }
}
