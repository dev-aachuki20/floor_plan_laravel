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
        'rota_id',
        'room_id',
        'time_slot',
        'speciality_id',
        'week_day_date',
        'created_at',
        'updated_at',
       
    ];

   
    public function rotaDetail()
    {
        return $this->belongsTo(Rota::class, 'rota_id', 'id');
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

  

}
