<?php

namespace App\Http\Requests\User;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $trustValidationRule = 'nullable';

        if ($user->is_system_admin) {
            $trustValidationRule = 'required';
        }
        return [
            'full_name'         => ['required', 'string', 'max:100'],
            'user_email'        => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'unique:users,user_email,NULL,id,deleted_at,NULL'],
            'password'          => ['required', 'string', 'min:8'],
            'role'              => ['required', 'exists:roles,id'],
            'trust'             => [$trustValidationRule, 'exists:trust,id'],
            'hospital'          => ['required', 'array'],
            'hospital.*'        => ['exists:hospital,id,deleted_at,NULL'],
            'speciality'        => ['required', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['required', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];
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
