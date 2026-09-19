<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active';
    }

    public function rules(): array
    {
        return [
            'book' => ['required', 'exists:books,id'],
            'accept_rules' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['book' => $this->route('book')?->id]);
    }

    public function messages(): array
    {
        return ['book.exists' => 'Buku yang dipilih tidak ditemukan.'];
    }
}