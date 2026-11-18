<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\ReadingChallenge;
use Illuminate\Support\Facades\Date;

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
        $user = request()->user();
        $today = Date::today();
        $challenge = ReadingChallenge::firstOrCreate([
            'user_id' => $user->id,
            'year' => $today->year,
            'month' => $today->month,
        ], ['target' => 4]);
        $booksRead = $user->loans()
            ->where('status', 'returned')
            ->whereBetween('tanggal_kembali', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])
            ->distinct('book_id')
            ->count('book_id');

        return view('home', [
            'bookCount' => Book::count(),
            'availableCount' => Book::where('is_circulating', true)->sum('stok_tersedia'),
            'activeBorrowings' => Loan::whereIn('status', ['pending', 'borrowed', 'overdue'])->count(),
            'recentBooks' => Book::latest()->take(4)->get(),
            'challenge' => $challenge,
            'booksRead' => $booksRead,
            'dueReminders' => $user->unreadNotifications()->where('type', \App\Notifications\LoanDueReminder::class)->latest()->take(5)->get(),
        ]);
    }
}
