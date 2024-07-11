<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use SoftDeletes, Notifiable;

    public $table = 'users';

    protected $fillable = [
        'uuid',
        'primary_role',
        'hospital',
        'user_email',
        'password',
        'full_name',
        'phone',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    protected static function boot ()
    {
        parent::boot();
        static::creating(function(User $model) {
            $model->uuid = Str::uuid();
        });
    }

    public function roles(){
        return $this->belongsToMany(Role::class);
    }

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', config('constant.roles.admin'))->exists();
    }

    public function getIsStaffAttribute()
    {
        return $this->roles()->where('id', config('constant.roles.staff'))->exists();
    }

    public function getIsCompanyAttribute()
    {
        return $this->roles()->where('id', config('constant.roles.company'))->exists();
    }


    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function profileImage()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'user_profile');
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->profileImage) {
            return $this->profileImage->file_url;
        }
        return "";
    }

    public function createdBy(){
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }
}
