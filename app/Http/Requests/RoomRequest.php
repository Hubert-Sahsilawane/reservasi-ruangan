<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pakai policy kalau mau lebih aman
    }

    public function rules(): array
    {
        return [
            'nama_ruangan' => 'required|string|max:255',
            'kapasitas' => 'nullable|integer',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:available,unavailable',
        ];
    }
}
