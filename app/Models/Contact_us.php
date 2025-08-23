<?php

namespace App\Models;

use App\Models\Scopes\SortingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact_us extends Model
{

    use HasFactory;
    protected $table = 'contact_us';
    protected $appends=['full_audio_path'];
    protected $guarded = [];
    protected $casts   = ['created_at' => 'date:Y-m-d', 'updated_at' => 'date:Y-m-d'];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

     public function getFullAudioPathAttribute()
    {
        return asset(getAudioPathFromDirectory($this->reply, 'contact'));
    }
}
