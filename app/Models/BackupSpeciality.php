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
        'speciality_id',
        'hospital_id',
        'created_at',
        'updated_at',
    ];

    public function speciality()
    {
        return $this->belongsTo(Speciality::class);
    }

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class,'hospital_id');
    }


}
