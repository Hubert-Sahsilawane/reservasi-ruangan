<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class ReservationCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembatalan harus diisi.',
            'reason.max' => 'Alasan pembatalan maksimal 255 karakter.',
        ];
    }
}
