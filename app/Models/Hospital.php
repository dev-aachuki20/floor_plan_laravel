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

    public function rotaTable()
    {
        return $this->hasMany(Rota::class, 'hospital_id');
    }

    public function trustDetails()
    {
        return $this->belongsTo(Trust::class, 'trust', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_hospital')
            ->withPivot('trust_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'hospital_id');
    }

    public function procedure()
    {
        return $this->hasMany(Procedure::class, 'procedure_id', 'id');
    }

    public function rotaRecords()
    {
        return $this->hasMany(Rota::class, 'hospital_id');
    }
}
