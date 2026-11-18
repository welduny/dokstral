<?php

namespace App\Http\Requests;

use App\Models\Book;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:32', Rule::unique('books', 'isbn')->ignore($this->route('book'))],
            'kategori' => [
                'nullable',
                Rule::in(array_unique([
                    ...Book::CATEGORIES,
                    $this->route('book')?->kategori,
                ])),
            ],
            'harga' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'lokasi_rak' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'cover_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'jumlah_total' => ['required', 'integer', 'min:1', 'max:100000'],
            'is_circulating' => ['boolean'],
        ];
    }
}
