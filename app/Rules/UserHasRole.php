<?php
// app/Rules/EmailHasRole.php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserHasRole implements Rule
{
    protected $email;
    protected $role;
    protected $type;
    protected $attribute;

    public function __construct($roles, $email='', $type='email')
    {
        $this->email = $email;
        $this->roles = is_array($roles) ? $roles : [$roles];
        $this->type = $type;
    }

    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        if($this->type == 'uuid'){
            $col = 'users.uuid';
            $val = $value;
        } else {
            $col = 'users.email';
            $val = $this->email;
        }
        return DB::table('users')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where($col, $val)
                ->whereIn('roles.id', $this->roles)
                ->exists();
    }

    public function message()
    {
        return trans('messages.required_role');
    }
}
