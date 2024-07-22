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
        $trustValidationRule = 'nullable';

        if ($user->is_system_admin) {
            $trustValidationRule = 'required';
        }

        return [
            'full_name'      => ['required', 'string', 'max:255', new TitleValidationRule],
            'user_email'     => ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', Rule::unique('users')->ignore($editUserId)],
            'password'       => ['nullable', 'string', 'min:8'],
            'trust'          => [$trustValidationRule, 'exists:trust,id'],
            'hospital'       => ['required', 'array'],
            'hospital.*'     => ['exists:hospital,id,deleted_at,NULL'],
            'speciality'     => ['required', 'exists:speciality,id,deleted_at,NULL'],
            'sub_speciality' => ['required', 'exists:sub_speciality,id,deleted_at,NULL'],
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
