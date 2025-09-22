<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FixedScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|time_format:H:i',
            'waktu_selesai' => 'required|time_format:H:i|after:waktu_mulai',
            'keterangan' => 'nullable|string',
        ];
    }
}
