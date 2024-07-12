<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Trust extends Model
{

    public $table = 'trust';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'trust_name',
        'trust_description',
        'created_at',
        'updated_at',
    ];

    public function hospitals()
    {
        return $this->hasMany(Hospital::class);
    }


}
