<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;


class Role extends Model
{
    // use SoftDeletes;

    public $table = 'roles';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        // 'deleted_at',
    ];

    protected $fillable = [
        'name',
        // 'guard_name',
        'created_at',
        'updated_at',
        // 'deleted_at',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'role_has_permission');
    }

}
