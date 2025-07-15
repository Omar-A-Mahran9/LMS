<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

       public function getQuestionAttribute()
        {
            return app()->getLocale() === 'ar' ? $this->question_ar : $this->question_en;
        }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }



    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_question_id');
    }
    public function readingPassage()
    {
        return $this->belongsTo(ReadingPassage::class);
    }

}
