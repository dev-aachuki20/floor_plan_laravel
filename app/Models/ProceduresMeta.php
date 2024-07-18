<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProceduresMeta extends Model
{
    public $table = 'procedures_meta';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'procedure_id',
        'meta_name',
        'meta_value',
        'created_at',
        'updated_at',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id', 'id');
    }

}
