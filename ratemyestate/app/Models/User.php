<?php

namespace App\Models;

use App\Notifications\MailResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use \Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

//class User extends Authenticatable implements MustVerifyEmail
class User extends Authenticatable
{
//    use Notifiable, HasApiTokens, HasRoles, MustVerifyEmailTrait;
    use Notifiable, HasApiTokens, HasRoles;

    protected $appends = ['full_profile_image', 'full_name'];

    protected $fillable = [
        'name', 'firstname', 'lastname', 'email', 'password', 'mobile', 'profile_image',
        'location', 'latitude', 'longitude', 'status', 'brand_type', 'company_id',
        'display_name', 'has_change_request', 'requested_display_name',
        'email_verified_at'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function getFullProfileImageAttribute()
    {
        if (!empty($this->profile_image)) {
            return url(Storage::url($this->profile_image));
        }
        return null;
    }

    public function getFullNameAttribute($value)
    {
        return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
    }

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class, 'company_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }
}
