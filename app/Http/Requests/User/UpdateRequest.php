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
        // $userId = $this->uuid;
        return [
            'full_name'      => ['required', 'string', 'max:100'],
            'password'       => ['nullable', 'string', 'min:8'],
            'trust'          => ['required', 'exists:trust,id'],
            'hospital'       => ['required', 'array'],
            'hospital.*'     => ['exists:hospital,id,deleted_at,NULL'],
            'speciality'     => ['required', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality' => ['required', 'exists:sub_speciality,id,deleted_at,NULL'],
        ];
    }

    public function messages()
    {
        return [
            'full_name.string'    => __('validation.string', ['attribute' => __('cruds.user.fields.name')]),
            'full_name.max'       => __('validation.max', ['attribute' => __('cruds.user.fields.name')]),
        ];
    }

    public function attributes()
    {
        return [];
    }
}
