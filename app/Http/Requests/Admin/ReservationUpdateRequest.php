<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReservationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // sudah dijaga di controller pakai role admin
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:255'
        ];
    }
}
