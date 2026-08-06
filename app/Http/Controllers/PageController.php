<?php

namespace App\Http\Controllers;

use App\Services\InstagramService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(InstagramService $instagram): View
    {
        return view('welcome', [
            'reels' => $instagram->reels(12),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function content(): View
    {
        return view('pages.content');
    }
}
