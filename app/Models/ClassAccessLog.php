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





    public function class()
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }


public function accessCode()
{
    return $this->belongsTo(ClassAccessCode::class, 'access_code_id');
}
public function student()
{
    return $this->belongsTo(Student::class, 'student_id');
}


}
