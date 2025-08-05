<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAccessLog extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];



        public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }


    public function students()
{
    return $this->hasManyThrough(
        Student::class,
        ClassAccessLog::class,
        'access_code_id', // Foreign key on ClassAccessLog
        'id',             // Foreign key on Student
        'id',             // Local key on ClassAccessCode
        'student_id'      // Local key on ClassAccessLog
    );
}

}
