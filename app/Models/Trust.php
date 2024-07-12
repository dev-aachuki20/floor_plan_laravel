<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Trust extends Model
{
    use SoftDeletes;

    public $table = 'trust';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'trust_name',
        'trust_description',
        'chair',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function hospitals()
    {
        return $this->hasMany(Hospital::class);
    }


}
