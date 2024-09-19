<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class UserActivity extends Model
{
    
    public $table = 'user_activities';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'hospital_id',
        'login_date',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }


}
