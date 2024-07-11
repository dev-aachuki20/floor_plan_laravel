<?php

namespace App\Http\Requests\Service;

use App\Rules\NoMultipleSpacesRule;
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
        $rules = [];

        $rules['name']            = ['required', 'regex:/^[a-zA-Z\s]+$/','string',  new NoMultipleSpacesRule, Rule::unique('services', 'name')->ignore($this->service, 'id')->whereNull('deleted_at')];
        $rules['slug']            = ['required', 'regex:/^[a-zA-Z0-9\-]+$/','string',  Rule::unique('services', 'slug')->ignore($this->service, 'id')->whereNull('deleted_at')];

        return $rules;
    }

    public function messages()
    {
        return [
           
        ];
    }

    public function attributes()
    {
        return [

        ];
    }

}
