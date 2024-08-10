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
            'rota_sessions' => 'required|array',
        ];
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [
           
        ];
    }
}
