<?php
namespace App\Http\Requests\RotaTable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailablityRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'rooms'                 => 'required|array',
            'rooms.*.id'            => 'required|exists:rooms,id,deleted_at,NULL',
            'rooms.*.room_records.AM.*'  => 'nullable',
            'rooms.*.room_records.PM.*'  => 'nullable',
            'rooms.*.room_records.EVE.*' => 'nullable',
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
        ];
    }
}
