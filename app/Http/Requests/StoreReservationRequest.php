<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id'    => 'required|exists:rooms,id',
            'start_time' => 'required|date|after:now',
            'end_time'   => 'required|date|after:start_time',
        ];
    }
}
