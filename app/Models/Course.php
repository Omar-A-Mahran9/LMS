<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    protected $appends = [
        'title',
        'description',
        'note',
        'full_image_path',
        'full_slide_image_path',
        'is_enrolled',
        'payment_type',
        'request_status',
        'is_full',
        'is_completed',
        'progress_percentage',
        'certificate_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')
            ->withPivot('payment_type', 'status', 'is_active')
            ->withTimestamps();
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
        return $this->hasMany(Section::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class);
    }

     public function quizzes()
    {
        return $this->hasMany(Quiz::class,'course_id');
    }

    public function homeworks()
    {
        return $this->hasMany(HomeWork::class,'course_id');
    }
    public function classes()
    {
        return $this->hasMany(CourseClass::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

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

    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Courses', 'default.svg'));
    }

    public function getFullSlideImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->slide_image, 'Courses/Slides', 'default.svg'));
    }

    public function getIsEnrolledAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId) return false;

        return $this->students()
            ->where('student_id', $studentId)
            ->where('is_active', 1)
            ->exists();
    }

    public function getPaymentTypeAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId) return null;

        return $this->students()
            ->where('student_id', $studentId)
            ->first()?->pivot->payment_type;
    }

    public function getRequestStatusAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId) return null;

        return $this->students()
            ->where('student_id', $studentId)
            ->first()?->pivot->status;
    }

    public function getIsFullAttribute()
    {
        if (is_null($this->max_students)) return false;

        $approvedCount = $this->students()
            ->where('status', 'approved')
            ->where('is_active', 1)
            ->count();

        return $approvedCount >= $this->max_students;
    }

    public function getProgressPercentageAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId) return 0;

        $videoIds = $this->videos()->pluck('id');
        $total = $videoIds->count();

        $completed = CourseVideoStudent::whereIn('course_video_id', $videoIds)
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->count();

        return $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    public function getIsCompletedAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId) return false;

        $videoIds = $this->videos()->pluck('id');
        $total = $videoIds->count();

        if ($total === 0) return false;

        $completed = CourseVideoStudent::whereIn('course_video_id', $videoIds)
            ->where('student_id', $studentId)
            ->where('is_completed', true)
            ->count();

        return $completed === $total;
    }

    public function getCertificateUrlAttribute()
    {
        $studentId = auth('api')->id();
        if (!$studentId || !$this->certificate_available) return null;

        return $this->is_completed
            ? route('student.certificates.download', ['course' => $this->id])
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Method
    |--------------------------------------------------------------------------
    */

    public function isStudentEnrolled($studentId)
    {
        return $this->students()
            ->where('student_id', $studentId)
            ->where('status', 'approved')
            ->where('is_active', 1)
            ->exists();
    }
}
