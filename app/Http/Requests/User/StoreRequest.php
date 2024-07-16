<?php

namespace App\Http\Requests\User;

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
            'full_name'         => ['required', 'string','min:50', 'max:100'],
            'user_email'        => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', 'unique:users,user_email,NULL,id,deleted_at,NULL'],
            'password'          => ['required', 'string', 'min:8'],
            'trust'             => ['required', 'exists:trust,id'],
            'role'              => ['required', 'exists:roles,id'],
            'hospital'          => ['required', 'exists:hospital,id,deleted_at,NULL'],
            'speciality'        => ['required', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality'    => ['required', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'  => __('validation.required', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.string'    => __('validation.string', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.min'       => __('validation.min', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.max'       => __('validation.max', ['attribute' => __('cruds.user.fields.name')]),
            'user_email.required' => __('validation.required', ['attribute' => __('cruds.user.fields.email')]),
            'user_email.email'    => __('validation.email', ['attribute' => __('cruds.user.fields.email')]),
            'user_email.regex'    => __('validation.not_regex', ['attribute' => __('cruds.user.fields.email')]),
            'user_email.unique'   => __('validation.unique', ['attribute' => __('cruds.user.fields.email')]),

        ];
    }

    public function attributes()
    {
        return [];
    }
}
