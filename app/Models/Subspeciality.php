<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Subspeciality extends Model
{
    use SoftDeletes;

    public $table = 'sub_speciality';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'parent_speciality_id',
        'sub_speciality_name',
        'sub_speciality_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class, 'parent_speciality_id','id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sub_speciality')
                    ->withPivot('speciality_id');
    }

}
