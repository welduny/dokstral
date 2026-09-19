<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home', [
            'bookCount' => Book::count(),
            'availableCount' => Book::sum('stok_tersedia'),
            'activeBorrowings' => Loan::whereIn('status', ['pending', 'borrowed', 'overdue'])->count(),
            'recentBooks' => Book::latest()->take(4)->get(),
        ]);
    }
}
