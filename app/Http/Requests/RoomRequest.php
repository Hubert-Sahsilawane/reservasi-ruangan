<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_ruangan' => 'required|string|max:255',
            'kapasitas'    => 'nullable|integer|min:1',
            'deskripsi'    => 'nullable|string',
            'status'       => 'required|in:aktif,non-aktif', // default di DB = non-aktif
        ];
    }
}
