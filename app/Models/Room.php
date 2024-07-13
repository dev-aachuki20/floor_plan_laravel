<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Room extends Model
{
    use SoftDeletes;

    public $table = 'rooms';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'hospital_id',
        'room_name',
        'room_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

}
