<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $table = 'notifications';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'notification_time',
        'notification_title',
        'notification_type',
        'notification_status',
        'created_at',
        'updated_at',
    ];

  
}
