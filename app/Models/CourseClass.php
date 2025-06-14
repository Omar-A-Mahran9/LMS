<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;

class CourseClass extends Model
{
    use HasFactory;
    protected $table = 'classes'; // specify table if not the plural of model

    protected $guarded = [];
    protected $appends = ['title', 'full_image_path','full_attachment_path','description'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];




    public function getFullImagePathAttribute()
    {
        return asset(getImagePathFromDirectory($this->image, 'courses_classes', 'default.svg'));
    }
    public function getFullAttachmentPathAttribute()
    {
        return getAttachmentPathFromDirectory($this->attachment, 'courses_classes');
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

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }
     public function videos()
    {
        return $this->hasMany(CourseVideo::class,'class_id');
    }
public function quizzes()
{
    return $this->hasMany(Quiz::class, 'class_id');
}

public function homeworks()
{
    return $this->hasMany(HomeWork::class, 'class_id');
}

public function students()
{
    return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id')->withTimestamps();
}


}

