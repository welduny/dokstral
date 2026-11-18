<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AdminBookController extends Controller
{
    private const ACTIVE_LOAN_STATUSES = ['pending', 'borrowed', 'overdue'];

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function create(): View
    {
        return view('admin.books.create', [
            'books' => Book::query()->latest()->get(),
        ]);
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('cover_file');
        $coverPath = null;

        try {
            $coverPath = $this->storeCoverFile($request->file('cover_file'));
            $data['cover_path'] = $coverPath;
            $data['cover_url'] = null;
            $data['stok_tersedia'] = (int) $data['jumlah_total'];
            $data['is_circulating'] = $request->boolean('is_circulating');
            $data['status'] = $data['is_circulating'] ? 'tersedia' : 'tidak_tersedia';
            $data['deskripsi'] = $data['deskripsi'] ?? null;

            Book::create($data);
        } catch (Throwable $exception) {
            if ($coverPath) {
                Storage::disk('public')->delete($coverPath);
            }

            throw $exception;
        }

        return redirect()->route('admin.books.create')->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }

    public function edit(Book $book): View
    {
        $activeLoanCount = $book->loans()->whereIn('status', self::ACTIVE_LOAN_STATUSES)->count();

        return view('admin.books.edit', compact('book', 'activeLoanCount'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $data = $request->safe()->except('cover_file');
        $oldCoverPath = null;
        $newCoverPath = null;

        try {
            DB::transaction(function () use ($request, $book, $data, &$oldCoverPath, &$newCoverPath): void {
                $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
                $activeLoans = $lockedBook->loans()->whereIn('status', self::ACTIVE_LOAN_STATUSES)->count();

                if ((int) $data['jumlah_total'] < $activeLoans) {
                    throw ValidationException::withMessages([
                        'jumlah_total' => "Stok total tidak boleh kurang dari {$activeLoans} eksemplar yang sedang direservasi atau dipinjam.",
                    ]);
                }

                $newTotal = (int) $data['jumlah_total'];
                $stockDelta = $newTotal - $lockedBook->jumlah_total;
                $data['stok_tersedia'] = min(
                    $newTotal - $activeLoans,
                    max(0, $lockedBook->stok_tersedia + $stockDelta),
                );
                $data['is_circulating'] = $request->boolean('is_circulating');

                if ($request->hasFile('cover_file')) {
                    $newCoverPath = $this->storeCoverFile($request->file('cover_file'));
                    $oldCoverPath = $lockedBook->cover_path;
                    $data['cover_path'] = $newCoverPath;
                    $data['cover_url'] = null;
                }

                $lockedBook->update($data);
                $lockedBook->syncAvailabilityStatus();
            });
        } catch (Throwable $exception) {
            if ($newCoverPath) {
                Storage::disk('public')->delete($newCoverPath);
            }

            throw $exception;
        }

        if ($oldCoverPath && $oldCoverPath !== $newCoverPath) {
            Storage::disk('public')->delete($oldCoverPath);
        }

        return redirect()->route('admin.books.create')->with('success', 'Data buku berhasil diperbarui.');
    }

    private function storeCoverFile(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        $path = $file->store('covers', 'public');

        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'cover_file' => 'Foto sampul gagal disimpan. Silakan coba lagi.',
            ]);
        }

        return $path;
    }
}
