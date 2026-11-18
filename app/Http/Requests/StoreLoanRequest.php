<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && $user->status === 'active'
            && $user->role !== 'admin';
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
