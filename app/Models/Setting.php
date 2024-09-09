<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $table = 'settings';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected $fillable = [
        'key',
        'value',
        'type',
        'display_name',
        'details',
        'group',
        'status',
        'created_at',
        'updated_at',
    ];

    
    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function image()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type','setting');
    }

    public function getImageUrlAttribute()
    {
        if($this->image){
            return $this->image->file_url;
        }
        return "";
    }

    public function video()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type','setting');
    }

    public function getVideoUrlAttribute()
    {
        if($this->video){
            return $this->video->file_url;
        }
        return "";
    }


   
}
