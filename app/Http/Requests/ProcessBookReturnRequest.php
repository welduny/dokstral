<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessBookReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'keterangan_kondisi' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
            'denda_tambahan' => ['nullable', 'integer', 'min:0', 'max:100000000'],
        ];
    }
}