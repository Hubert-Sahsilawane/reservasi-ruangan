<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class ReservationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'       => 'required|exists:rooms,id',
            'tanggal'       => 'required|date|after_or_equal:today',
            'hari'          => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai'   => 'required|time_format:H:i',
            'waktu_selesai' => 'required|time_format:H:i|after:waktu_mulai',
            'keterangan'    => 'nullable|string|max:255',
        ];
    }
}
