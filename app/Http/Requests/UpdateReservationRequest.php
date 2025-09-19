<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'     => 'sometimes|required|in:pending,approved,rejected,cancelled',
            'start_time' => 'sometimes|required|date|after:now',
            'end_time'   => 'sometimes|required|date|after:start_time',
        ];
    }
}
