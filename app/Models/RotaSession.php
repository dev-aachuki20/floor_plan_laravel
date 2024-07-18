<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class RotaSession extends Model
{
    use SoftDeletes;

    public $table = 'rota_sessions';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'hospital_id',
        'user_id',
        'procedure_id',
        'time_slot',
        'status_id',
        'scheduled',
        'session_description',
        'session_released',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id', 'id');
    }

    public function sessionStatus()
    {
        return $this->belongsTo(SessionStatus::class, 'status_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

}
