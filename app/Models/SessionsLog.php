<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionsLog extends Model
{
    public $table = 'sessions_log';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'session_id',
        'offering_id',
        'action_time',
        'role',
        'booking_status',
        'created_at',
        'updated_at',
    ];

    public function rotaSession()
    {
        return $this->belongsTo(RotaSession::class, 'session_id', 'id');
    }

    public function sessionsOffering()
    {
        return $this->belongsTo(SessionsOffering::class, 'offering_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role', 'id');
    }

}
