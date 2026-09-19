<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminBookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function create(): View
    {
        return view('admin.books.create');
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['stok_tersedia'] = $data['jumlah_total'];
        $data['status'] = ($data['is_circulating'] ?? true) ? 'tersedia' : 'tidak_tersedia';
        $data['is_circulating'] = $request->boolean('is_circulating', true);

        Book::create($data);

        return redirect()->route('admin.books.create')->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }
}