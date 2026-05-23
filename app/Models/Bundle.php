<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $appends = [

        'full_image_path',

        'classes_count',

        'codes_count',
    ];


    protected $casts = [

        'is_active' => 'boolean',

        'created_at' => 'date:Y-m-d',

        'updated_at' => 'date:Y-m-d',
    ];


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Bundles', 'default.svg'));
    }


    public function getClassesCountAttribute()
    {
        return $this->classes()->count();
    }


    public function getCodesCountAttribute()
    {
        return $this->codes()->count();
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function classes()
    {
        return $this->belongsToMany(
            CourseClass::class,
            'bundle_class',
            'bundle_id',
            'class_id'
        );
    }


    public function codes()
    {
        return $this->hasMany(
            BundleAccessCode::class,
            'bundle_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function activeCodes()
    {
        return $this->codes()
            ->where('is_active', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return $this->is_active;
    }
}
