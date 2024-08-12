<?php
namespace App\Http\Requests\RotaTable;

use Illuminate\Foundation\Http\FormRequest;

class SaveRotaRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'quarter_id'            => 'nullable',
            'week_days'             => ['required', 'array','size:7'],
            'week_days.*'           => ['required', 'date'],
            'hospital_id'           => 'required|exists:hospital,id,deleted_at,NULL',
            'rooms'                 => 'required|array',
            'rooms.*.id'            => 'required|exists:rooms,id,deleted_at,NULL',
            'rooms.*.room_records.*.AM'  => 'nullable',
            'rooms.*.room_records.*.PM'  => 'nullable',
            'rooms.*.room_records.*.EVE' => 'nullable',
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
        ];
    }
}
