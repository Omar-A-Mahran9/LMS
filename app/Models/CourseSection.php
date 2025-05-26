<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
  protected $guarded = [];
    protected $appends = ['title'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class)->orderBy('order');
    }
}

