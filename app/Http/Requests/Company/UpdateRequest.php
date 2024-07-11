<?php

namespace App\Http\Requests\Company;

use App\Rules\NoMultipleSpacesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;


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
        if($this->step_no == 1){
            $user = User::where('uuid',$this->user_id)->first();

            $company_id =  $user->company->id;

            $rules['company_name']          = ['required', 'regex:/^[a-zA-Z\s]+$/','string',  new NoMultipleSpacesRule, Rule::unique('companies', 'company_name')->ignore($company_id, 'id')->whereNull('deleted_at')];
        
            $rules['first_name']            = ['nullable', 'regex:/^[a-zA-Z\s]+$/','string',  new NoMultipleSpacesRule];
            
            $rules['last_name']             = ['nullable', 'regex:/^[a-zA-Z\s]+$/','string',  new NoMultipleSpacesRule];
             
            $rules['email']                 = ['required', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i', Rule::unique('users', 'email')->ignore($user->id, 'id')->whereNull('deleted_at')];
            
            $rules['phone']                 = ['nullable', 'numeric', 'regex:/^[0-9]{7,15}$/', Rule::unique('users', 'phone')->ignore($user->id, 'id')->whereNull('deleted_at')];
    
            $rules['password']              = ['nullable', 'string', 'min:8', 'max:15', /* 'regex:/^(?!.*\s)(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/' */];
    
            $rules['company_website']       = ['nullable'];
            $rules['total_employees']       = ['nullable'];
            $rules['founding_year']         = ['nullable'];
    
            $rules['tagline']               = ['nullable'];
    
            $rules['admin_contact_phone']   = ['nullable', 'numeric', 'regex:/^[0-9]{7,15}$/'];
    
            $rules['sales_email']           = ['nullable', 'email', 'regex:/^(?!.*[\/]).+@(?!.*[\/]).+\.(?!.*[\/]).+$/i'];
    
            $rules['company_logo']          = ['nullable', 'image', 'max:'.config('constant.profile_max_size'), 'mimes:jpeg,png,jpg'];
        }

        if($this->step_no == 2){
            $rules['description']           = ['nullable'];
        }

        if($this->step_no == 3){

            $rules['location.*.country']            = [];
            $rules['location.*.city']               = [];
            $rules['location.*.street']             = [];
            $rules['location.*.total_employees']    = [];
            $rules['location.*.contact_number']     = [];
        }

        if($this->step_no == 4){

            $rules['minimum_project_size']            = [];
            $rules['average_hourly_rate']             = [];
          
        }

        if($this->step_no == 5){

        }

        if($this->step_no == 6){

            $rules['portfolios.*.client_name']           = [];
            $rules['portfolios.*.client_website']        = [];
            $rules['portfolios.*.project_title']         = [];
            $rules['portfolios.*.project_industry']      = [];
            $rules['portfolios.*.timeline']              = [];
            $rules['portfolios.*.project_cost']          = [];
            $rules['portfolios.*.project_description']   = [];
            $rules['portfolios.*.screenshot']            = ['nullable', 'image', 'mimes:jpeg,png,jpg'];

        }

        if($this->step_no == 7){

            $rules['clients.*.client_name']           = [];
            $rules['clients.*.profile_image']         = ['nullable', 'image', 'mimes:jpeg,png,jpg'];

        }


        // dd($this->all());
        
        
        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.required', ['attribute' => strtolower(__('cruds.user.fields.name'))]),
            'name.regex' => __('validation.regex', ['attribute' => strtolower(__('cruds.user.fields.name'))]),
            'name.string' => __('validation.string', ['attribute' => strtolower(__('cruds.user.fields.name'))]),
            'name.max' => __('validation.max.string', ['attribute' => strtolower(__('cruds.user.fields.name')), 'max' => ':max']),

            'password.required' => __('validation.required', ['attribute' => strtolower(__('cruds.user.fields.password'))]),
            'password.string' => __('validation.string', ['attribute' => strtolower(__('cruds.user.fields.password'))]),
            'password.min' => __('validation.min.string', ['attribute' => strtolower(__('cruds.user.fields.password')), 'min' => ':min']),
            'password.max' => __('validation.max.string', ['attribute' => strtolower(__('cruds.user.fields.password')), 'max' => ':max']),
            // 'password.regex' => __('validation.password.regex', ['attribute' => strtolower(__('cruds.user.fields.password'))]),

            'email.required' => __('validation.required', ['attribute' => strtolower(__('cruds.user.fields.email'))]),
            'email.ends_with' => __('validation.ends_with', ['attribute' => strtolower(__('cruds.user.fields.email'))]),
            'email.unique' => __('validation.unique', ['attribute' => strtolower(__('cruds.user.fields.email'))]),
        ];
    }

    public function attributes()
    {
        return [

           'location.*.country' => 'country',
           'location.*.city'    => 'city',
           'location.*.street'  => 'street',
           'location.*.total_employees' => 'total employees',
           'location.*.contact_number'  => 'contact number',

           'portfolios.*.client_name'    => 'client name',
           'portfolios.*.client_website' => 'client website',
           'portfolios.*.project_title'  => 'project title',
           'portfolios.*.project_industry'  => 'Project industry',
           'portfolios.*.timeline'          => 'timeline',
           'portfolios.*.project_cost'      => 'project cost',
           'portfolios.*.project_description'      => 'project description',
           'portfolios.*.screenshot' => 'screenshot',

           'clients.*.client_name'   => 'client name',
           'clients.*.profile_image' => 'profile image',


        ];
    }
}
