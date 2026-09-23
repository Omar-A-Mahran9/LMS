<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = [];
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
        'selected_answer_ids' => 'array',
    ];

    // Ids of every option the student picked (older rows only have quiz_answer_id)
    public function selectedIds(): array
    {
        if (!empty($this->selected_answer_ids)) {
            return array_map('intval', $this->selected_answer_ids);
        }

        return $this->quiz_answer_id ? [(int) $this->quiz_answer_id] : [];
    }

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
