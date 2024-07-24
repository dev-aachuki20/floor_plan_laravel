<?php

namespace App\Http\Requests\RotaTable;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hospital_id'           => 'required|exists:hospital,id',
            'user_id'               => 'required|exists:users,id',
            'status_id'             => 'required|exists:session_status,id',
            // 'procedure_id'          => 'required|exists:procedures,id',
            // 'time_slot'             => 'required|string|in:AM,PM,EVE',
            // 'scheduled'             => 'nullable|date',
            // 'session_description'   => 'required|string',
            // 'session_released'      => 'required|boolean',
            'rooms' => 'required|array',
            'rooms.*.session_description' => 'required|string',
            'rooms.*.session_released' => 'required|boolean',
            'rooms.*.time_slots' => 'required|array',
            'rooms.*.time_slots.AM.procedure_id' => 'nullable|integer|exists:procedures,id',
            'rooms.*.time_slots.PM.procedure_id' => 'nullable|integer|exists:procedures,id',
            'rooms.*.time_slots.EVE.procedure_id' => 'nullable|integer|exists:procedures,id',
        ];
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [
            'hospital_id'   => 'hospital',
            'user_id'       => 'user',
            'procedure_id'  => 'procedure',
            'status_id'     => 'status'
        ];
    }
}
