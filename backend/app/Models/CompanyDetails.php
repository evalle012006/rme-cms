<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class CompanyDetails extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'company_details';
    protected $appends = ['full_company_logo'];

    protected $fillable = [
        'company_name',
        'slug',
        'brand_type',
        'bio',
        'contact_number',
        'website',
        'logo',
        'logo_thumb',
        'location',
        'is_from',
        'status'
    ];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'company_name'
            ]
        ];
    }

    function getFullCompanyLogoAttribute()
    {
        if (!empty($this->logo)) {
            return absoluteStorageUrl($this->logo);
        }
        return null;
    }

    public function companyProperties()
    {
        return $this->hasMany(Properties::class, 'developer_name', 'id');
    }

    public function companyUsers()
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }
}
