<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{

    public $table = 'quarters';
    public $timestamps = true;

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'quarter_name',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',

    ];


    public function rotaSession()
    {
        return $this->hasMany(RotaSession::class, 'quarter_id');
    }



}
