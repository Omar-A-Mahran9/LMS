<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseVideoStudent extends Model
{
        protected $table = 'course_video_student'; // specify table if not the plural of model

    use HasFactory;
     protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function getProgressPercentAttribute()
{
    $duration = $this->courseVideo->duration_seconds ?? 1;
    return round(($this->watch_seconds / $duration) * 100);
}

public function courseVideo()
{
    return $this->belongsTo(CourseVideo::class);
}
}
