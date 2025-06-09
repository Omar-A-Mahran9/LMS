<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title', 'full_image_path','full_slide_image_path','description', 'note','is_enrolled','payment_type','request_status'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];
    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Courses', 'default.svg'));
    }
    public function getFullSlideImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->slide_image, 'Courses/Slides', 'default.svg'));
    }


    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    public function getNoteAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->note_ar : $this->note_en;
    }


    public function instructor()
    {
        return $this->belongsTo(Admin::class, 'instructor_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategories()
    {
        return $this->belongsToMany(Category::class, 'course_category');
    }

    public function sections()
    {
        return $this->hasMany(CourseSection::class);
    }
    public function videos()
    {
        return $this->hasMany(CourseVideo::class);
    }


public function getIsEnrolledAttribute()
{
    if (!auth('api')->check()) return false;

    return $this->students()
        ->where('student_id', auth('api')->id())
        ->reorder() // remove orderBy to avoid ambiguous column inside exists()
        ->exists();
}

public function getPaymentTypeAttribute()
{
    if (!auth('api')->check()) return null;

    $studentId = auth('api')->id();

    // Find the pivot row for this student on this course
    $pivot = $this->students()->where('student_id', $studentId)->first()?->pivot;

    return $pivot ? $pivot->payment_type : null;
}
public function getRequestStatusAttribute()
{
    if (!auth('api')->check()) return null;

    $studentId = auth('api')->id();

    $pivot = $this->students()->where('student_id', $studentId)->first()?->pivot;

    return $pivot ? $pivot->status : null; // Or 'status' depending on your pivot column name
}


public function students()
{
    return $this->belongsToMany(Student::class, 'course_student')
                ->withPivot('payment_type', 'status', 'is_active')
                ->withTimestamps();
}





}
