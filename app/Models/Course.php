<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model                  
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['name', 'full_image_path','full_slide_image_path','description', 'note'];
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
    return asset(getImagePathFromDirectory($this->slide_image, 'Courses/Slides', 'default-slide.svg'));
}


    public function getNameAttribute()
{
    return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
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
    return $this->belongsTo(User::class, 'instructor_id');
}
public function subCategories()
{
    return $this->belongsToMany(Category::class, 'category_course', 'course_id', 'category_id');
}


}
