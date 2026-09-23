<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['question'];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];
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
    // How many options the student may pick: a multiple choice question with several
    // correct answers (set in the dashboard) allows picking that many
    public function maxSelections(): int
    {
        if ($this->type !== 'multiple_choice') {
            return 1;
        }

        return max(1, $this->answers->where('is_correct', 1)->count());
    }

    // Right only when the picked options are exactly the correct ones
    public function isCorrectSelection(array $selectedIds): bool
    {
        $correctIds = $this->answers->where('is_correct', 1)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $selectedIds = collect($selectedIds)->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

        return !empty($selectedIds) && $selectedIds === $correctIds;
    }

    public function readingPassage()
    {
        return $this->belongsTo(ReadingPassage::class);
    }

}
