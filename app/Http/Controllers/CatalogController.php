<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in(Book::CATEGORIES)],
            'availability' => ['nullable', 'in:available,all'],
        ]);

        $books = Book::query()
            ->with(['reviews' => fn ($query) => $query->latest()->limit(3), 'reviews.user'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->when($filters['q'] ?? null, function ($query, $term): void {
                $query->where(function ($query) use ($term): void {
                    $query->where('judul', 'like', '%'.$term.'%')
                        ->orWhere('penulis', 'like', '%'.$term.'%')
                        ->orWhere('isbn', 'like', '%'.$term.'%');
                });
            })
            ->when($filters['category'] ?? null, function ($query, string $category): void {
                $query->whereRaw('LOWER(kategori) = ?', [strtolower($category)]);
            })
            ->when(($filters['availability'] ?? null) === 'available', fn ($query) => $query->where('is_circulating', true)->where('stok_tersedia', '>', 0))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $favoriteBookIds = $request->user()
            ? $request->user()->bookFavorites()->pluck('book_id')->all()
            : [];
        $reviewableBookIds = $request->user()
            ? $request->user()->loans()->where('status', 'returned')->distinct()->pluck('book_id')->all()
            : [];

        return view('welcome', [
            'books' => $books,
            'categories' => Book::CATEGORIES,
            'favoriteBookIds' => $favoriteBookIds,
            'reviewableBookIds' => $reviewableBookIds,
            'filters' => $filters,
        ]);
    }
}