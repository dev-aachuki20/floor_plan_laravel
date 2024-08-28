<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSpeciality extends Model
{
    public $table = 'backup_speciality';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'hospital_id',
        'days',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class);
    }


}
