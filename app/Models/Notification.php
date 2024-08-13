<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = "notifications";

    protected $primarykey = "id";

    protected $fillable = [
        'id',
        'type',
        'notifiable',
        'data',
        'subject',
        'message',
        'section',
        'notification_type',
        'rota_session_id',
        'created_by',
        'read_at',        
    ];

    protected $dates = [
        'updated_at',
        'created_at',
        'deleted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'id' => 'string'
    ];


    public function notifyUser()
    {
        return $this->belongsTo(User::class,'notifiable_id','id');
    }

    public function rotaSession()
    {
        return $this->belongsTo(RotaSession::class,'rota_session_id','id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }


}
