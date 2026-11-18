<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\ReadingChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class BookCommunityController extends Controller
{
    public function toggleFavorite(Book $book): RedirectResponse
    {
        $favorite = request()->user()->bookFavorites()->where('book_id', $book->id)->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Buku dihapus dari favorit.';
        } else {
            request()->user()->bookFavorites()->firstOrCreate(['book_id' => $book->id]);
            $message = 'Buku disimpan ke favorit.';
        }

        return back()->with('success', $message);
    }

    public function storeReview(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless(
            $request->user()->loans()->where('book_id', $book->id)->where('status', 'returned')->exists(),
            403,
            'Ulasan hanya dapat diberikan setelah buku dikembalikan.'
        );

        BookReview::updateOrCreate(
            ['user_id' => $request->user()->id, 'book_id' => $book->id],
            $data
        );

        return back()->with('success', 'Ulasan bukumu berhasil disimpan.');
    }

    public function updateChallenge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'integer', 'between:1,50'],
        ]);
        $now = Date::now();

        ReadingChallenge::updateOrCreate(
            ['user_id' => $request->user()->id, 'year' => $now->year, 'month' => $now->month],
            ['target' => $data['target']]
        );

        return back()->with('success', 'Target baca bulanan diperbarui.');
    }
}