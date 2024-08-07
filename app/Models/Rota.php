<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Rota extends Model
{
   
    public $table = 'rota';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'uuid',
        'quarter_id',
        'hospital_id',
        'week_no',
        'week_start_date',
        'week_end_date',
        'session_released',
        'session_description',
        'created_by',
        'created_at',
        'updated_at',

    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function (Rota $model) {

            $model->uuid = Str::uuid();

            $model->created_by = auth()->user() ? auth()->user()->id : null;
        });
    }

    public function quarterDetail()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id', 'id');
    }

    public function rotaSession()
    {
        return $this->hasMany(RotaSession::class, 'rota_id');
    }

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
   
  
}
