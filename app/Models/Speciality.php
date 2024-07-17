<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Speciality extends Model
{
    use SoftDeletes;

    public $table = 'speciality';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'speciality_name',
        'speciality_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function subSpeciality()
    {
        return $this->hasMany(SubSpeciality::class);
    }

  
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sub_speciality')
                    ->withPivot('sub_speciality_id');
    }


}
