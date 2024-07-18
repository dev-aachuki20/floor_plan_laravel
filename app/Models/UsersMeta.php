<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersMeta extends Model
{
    public $table = 'users_meta';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'user_id',
        'meta_name',
        'meta_value',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
