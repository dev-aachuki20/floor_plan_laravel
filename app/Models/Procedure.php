<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use SoftDeletes;

    public $table = 'procedures';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'finance_id',
        'procedures_name',
        'procedures_description',
        'required_roles',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'hospital_id',
    ];

    public function finance()
    {
        return $this->belongsTo(Finance::class, 'finance_id', 'id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }
}
