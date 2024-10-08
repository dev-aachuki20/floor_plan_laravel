<?php

namespace App\Http\Requests\User;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\TitleValidationRule;

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
        $user = Auth::user();

        $rules = [
            'full_name'         => ['required', 'string', 'max:255',new TitleValidationRule],
            'user_email'        => ['required', 'email:dns', 'regex:/^(?!.*\s)(?!.*[\/]).+@.+\..+$/i', 'unique:users,user_email,NULL,id'],

            'password'          => ['required', 'string', 'min:8'],
            'password_confirmation'  => ['required', 'string', 'min:8','same:password'],
            
            'role'              => ['required', 
                Rule::exists('roles', 'id')->whereNot('id', config('constant.roles.system_admin'))
            ],

            'trust'             => ['nullable', 'exists:trust,id'],
            'hospital'          => ['required', 'array'],
            'hospital.*'        => ['exists:hospital,id,deleted_at,NULL'],
            'speciality'        => ['nullable', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['nullable', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];

        if ($user->is_system_admin) {
            $rules['trust']  = ['required', 'exists:trust,id'];
        }

        if($this->role == config('constant.roles.booker')){
            $rules['speciality']        = ['nullable'];
            $rules['sub_speciality']    = ['nullable'];
        }
        

        return  $rules;
    }

    public function messages()
    {
        return [];
    }

    public function attributes()
    {
        return [
            'full_name' => 'name',
            'user_email' => 'email'
        ];
    }
}
