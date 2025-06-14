<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeWorkAttempt extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'started_at' => 'datetime',
         'submitted_at' => 'datetime',
    ];
    protected $dates = ['started_at', 'submitted_at'];


     public function homework()
    {
        return $this->belongsTo(HomeWork::class,'home_work_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(HomeWorkAttemptAnswer::class);
    }

}
