<?php

namespace App\Http\Requests\User;

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
        $userId = $this->uuid;
        return [
            'full_name'      => ['nullable', 'string', 'min:50', 'max:100'],
            'user_email'     => ['nullable', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', Rule::unique('users', 'user_email')->ignore($userId, 'uuid')->whereNull('deleted_at')],
            'password'       => ['nullable', 'string', 'min:8'],
            'trust'          => ['nullable', 'exists:trust,id'],
            'role'           => ['nullable', 'exists:roles,id'],
            'hospital'       => ['nullable', 'exists:hospital,id,deleted_at,NULL'],
            'speciality'     => ['nullable', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality' => ['nullable', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];
    }

    public function messages()
    {
        return [
            'full_name.string'    => __('validation.string', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.min'       => __('validation.min', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.max'       => __('validation.max', ['attribute' => __('cruds.user.fields.name')]),
            'user_email.email'    => __('validation.email', ['attribute' => __('cruds.user.fields.email')]),
            'user_email.regex'    => __('validation.not_regex', ['attribute' => __('cruds.user.fields.email')]),
        ];
    }

    public function attributes()
    {
        return [];
    }
}
