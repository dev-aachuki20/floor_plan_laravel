<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kit extends Model
{
    use SoftDeletes;

    public $table = 'kits';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'procedure_id',
        'hospital_id',
        'kit_name',
        'kit_status',
        'kit_description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hospitalDetail()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id', 'id');
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id', 'id');
    }

}
