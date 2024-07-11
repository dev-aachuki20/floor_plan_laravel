<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    public $table = 'settings';

    protected $fillable = [
        'session_at_risk',
        'session_released',
        'session_reassign',
        'session_fallback',
        'allow_registration',
    ];


}
