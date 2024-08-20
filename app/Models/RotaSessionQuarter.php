<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;


class RotaSessionQuarter extends Model
{
    public $table = 'rota_session_quarters';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [

        'quarter_no',
        'quarter_year',
        'hospital_id',
        'room_id',
        'time_slot',
        'day_name',
        'speciality_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',

    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (RotaSessionQuarter $model) {
            $model->created_by = auth()->user() ? auth()->user()->id : null;
        });

        static::updating(function (RotaSessionQuarter $model) {
            $model->updated_by = auth()->user() ? auth()->user()->id : null;
            $model->save();
        });
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }



}
