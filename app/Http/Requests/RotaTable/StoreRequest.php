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
            'quarter_id'            => 'required|exists:quarters,id',
            'hospital_id'           => 'required|exists:hospital,id,deleted_at,NULL',
            'rooms'                 => 'required|array',
            'rooms.*.id'            => 'required|exists:rooms,id,deleted_at,NULL',
           
            'rooms.*.time_slots'    => 'required|array',
            'rooms.*.time_slots.AM.procedure_id' => 'nullable|integer|exists:procedures,id',

            'rooms.*.room_records.*.AM'  => 'nullable|string',
            'rooms.*.room_records.*.PM'  => 'nullable|string',
            'rooms.*.room_records.*.EVE' => 'nullable|string',

        ];
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [
            'quarter_id'    => 'quarter',
            'hospital_id'   => 'hospital',
            'user_id'       => 'user',
            'procedure_id'  => 'procedure',
            'status_id'     => 'status'
        ];
    }
}
