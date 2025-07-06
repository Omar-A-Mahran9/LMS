<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['name', 'full_image_path', 'description'];

    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    // Return a generic name or a composed one
    public function getNameAttribute()
    {
        return 'Certificate #' . $this->certificate_id;
    }

    public function getFullImagePathAttribute()
    {
        return $this->file_path ? asset($this->file_path) : null;
    }

    public function getDescriptionAttribute()
    {
        return "Certificate issued for student ID {$this->student_id} for course ID {$this->course_id}";
    }
}
