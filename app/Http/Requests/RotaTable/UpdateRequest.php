<?php

namespace App\Http\Requests\RotaTable;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
            'procedure_id'          => 'required|exists:procedures,id',
            'time_slot'             => 'required|string',
            'status_id'             => 'required|exists:session_status,id',
            'scheduled'             => 'nullable|date',
            'session_description'   => 'required|string',
            'session_released'      => 'required|boolean',
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
