<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class GuideController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('guide');
    }
}
