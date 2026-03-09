<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDevice extends Model
{
    use HasFactory;
    protected $fillable = [
     'student_id',
     'device_id',
     'device_name',
     'device_type',
     'ip'
    ];
}
