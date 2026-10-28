<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $appends = [

        'title',

        'description',

        'full_image_path',

        'final_price',

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

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : ($this->title_en ?: $this->title_ar);
    }


    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : ($this->description_en ?: $this->description_ar);
    }


    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Bundles', 'default.svg'));
    }


    /** Price the student pays: the discounted one when set. */
    public function getFinalPriceAttribute()
    {
        return $this->discount_price !== null ? $this->discount_price : $this->price;
    }


    public function getClassesCountAttribute()
    {
        return $this->relationLoaded('classes') ? $this->classes->count() : $this->classes()->count();
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


    /** Students who activated the bundle with a code. */
    public function activations()
    {
        return $this->hasMany(BundleStudent::class);
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'bundle_student')
            ->withPivot(['access_code_id', 'used_at'])
            ->withTimestamps();
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


    public function scopeOwnedBy(Builder $query, $studentId): Builder
    {
        return $query->whereHas('activations', fn($q) => $q->where('student_id', $studentId));
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


    public function isOwnedBy($studentId): bool
    {
        return $studentId && $this->activations()->where('student_id', $studentId)->exists();
    }


    /**
     * The student activated a bundle that contains this class.
     * Students keep their bundles even when the bundle is later deactivated (stopped selling).
     */
    public static function grantsClass($studentId, $classId): bool
    {
        if (!$studentId || !$classId) {
            return false;
        }

        return BundleStudent::where('student_id', $studentId)
            ->whereIn('bundle_id', fn($q) => $q->select('bundle_id')->from('bundle_class')->where('class_id', $classId))
            ->exists();
    }
}
