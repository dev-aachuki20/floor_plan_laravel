<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Hospital extends Model
{

    public $table = 'hospital';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'hospital_name',
        'hospital_description',
        'created_at',
        'updated_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function trust()
    {
        return $this->belongsTo(Trust::class);
    }

}
