<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;

class CourseVideo extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title', 'full_image_path','description'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'Courses_videos', 'default.svg'));
    }

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

      public function class()
    {
        return $this->belongsTo(CourseClass::class);
    }

public function viewers()
{
    return $this->belongsToMany(Student::class, 'course_video_student')
        ->withPivot(['is_completed', 'watch_seconds', 'last_watched_at', 'views'])
        ->withTimestamps();
}

public function watchers()
{
    return $this->belongsToMany(Student::class, 'course_video_student')
        ->withPivot('watch_seconds')
        ->withTimestamps();
}

public function studentProgress()
{
    return $this->hasMany(CourseVideoStudent::class);
}


}

