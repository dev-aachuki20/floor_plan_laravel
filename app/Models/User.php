<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
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
        'created_by',
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

            $model->created_by = auth()->user() ? auth()->user()->id : null;
        });
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            // 'user_email' => $this->user_email,
            // 'role' => $this->role->role_name,
            // 'hospital' => $this->hospital->hospital_name,
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class,'primary_role','id');
    }


    public function getIsSystemAdminAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.system_admin');
    }

    public function getIsTrustAdminAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.trust_admin');
    }

    public function getIsHospitalAdminAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.hospital_admin');
    }

    public function getIsSpecialityLeadAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.speciality_lead');
    }

    public function getIsStaffCoordinatorAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.staff_coordinator');
    }

    public function getIsAnestheticLeadAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.anesthetic_lead');
    }

    public function getIsBookerAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.booker');
    }

    public function getIsChairAttribute()
    {
        return $this->role && $this->role->id === config('constant.roles.chair');
    }


    public function uploads()
    {
        return $this->morphMany(Uploads::class, 'uploadsable');
    }

    public function profileImage()
    {
        return $this->morphOne(Uploads::class, 'uploadsable')->where('type', 'user_profile');
    }


    public function createdBy(){
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class,'hospital','id');
    }

    
}
