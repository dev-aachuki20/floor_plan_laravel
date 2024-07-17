<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Hospital extends Model
{
    use SoftDeletes;

    public $table = 'hospital';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'hospital_name',
        'hospital_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // public function users()
    // {
    //     return $this->hasMany(User::class,'hospital');
    // }

    public function trustDetails()
    {
        return $this->belongsTo(Trust::class, 'trust', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hospital')
            ->withPivot('trust_id');
    }
}
