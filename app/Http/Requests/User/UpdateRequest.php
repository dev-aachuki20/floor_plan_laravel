<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Rules\TitleValidationRule;

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
        $editeUserDetail = User::where('uuid', $this->uuid)->first();
        $editUserId = $editeUserDetail ? $editeUserDetail->id : null;

        $user = Auth::user();

        $rules = [
            'full_name'      => ['required', 'string', 'max:255', new TitleValidationRule],
            'user_email'     => ['required', 'email:dns', 'regex:/^(?!.*\s)(?!.*[\/]).+@.+\..+$/i', Rule::unique('users')->ignore($editUserId)],
            'password'       => ['nullable', 'string', 'min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])(?!.*\s)[A-Za-z\d\W_]{8,}$/'],
            'role'              => ['required', 
                Rule::exists('roles', 'id')->whereNot('id', config('constant.roles.system_admin'))
            ],
            'trust'          => ['nullable', 'exists:trust,id'],
            'hospital'       => ['required', 'array'],
            'hospital.*'     => ['exists:hospital,id,deleted_at,NULL'],
            'speciality'     => ['nullable', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality' => ['nullable', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];

        if ($user->is_system_admin) {
            $rules['trust']  = ['required', 'exists:trust,id'];
        }

        if($this->role == config('constant.roles.booker')){
            $rules['speciality']        = ['nullable'];
            $rules['sub_speciality']    = ['nullable'];
        }

        if($this->password){
            $rules['password_confirmation']  = ['required', 'string', 'min:8','regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])(?!.*\s)[A-Za-z\d\W_]{8,}$/','same:password'];
        }

        return $rules;
       
    }

    public function messages()
    {
        return [
            'password.regex' => trans('messages.password_regex'),
            'password_confirmation.regex' => trans('messages.password_regex')
        ];
    }

    public function attributes()
    {
        return [
            'full_name' => 'name',
            'user_email' => 'email'
        ];
    }
}
