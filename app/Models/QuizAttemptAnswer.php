<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title', 'full_image_path','full_slide_image_path','description', 'note'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class);
    }

    public function answer()
    {
        return $this->belongsTo(QuizAnswer::class, 'quiz_answer_id');
    }
}
