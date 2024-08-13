<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;


class RotaSession extends Model
{
    public $table = 'rota_sessions';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'uuid',
        'quarter_id',
        'week_no',
        'hospital_id',
        'room_id',
        'time_slot',
        'speciality_id',
        'week_day_date',
        'created_by',
        'created_at',
        'updated_at',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RotaSession $model) {

            $model->uuid = Str::uuid();

            $model->created_by = auth()->user() ? auth()->user()->id : null;
        });
    }

    public function quarterDetail()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id', 'id');
    }

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }

    public function roomDetail()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }


    public function specialityDetail()
    {
        return $this->belongsTo(Speciality::class, 'speciality_id', 'id');
    }


    public function users()
    {
        return $this->belongsToMany(User::class, 'rota_session_users')
                    ->withPivot(['role_id','status']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }



}
