<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionsOffering extends Model
{
    use SoftDeletes;

    public $table = 'sessions_offering';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'session_id',
        'offering_time',
        'offering_count',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function rotaSession()
    {
        return $this->belongsTo(RotaSession::class, 'session_id', 'id');
    }
  
}
