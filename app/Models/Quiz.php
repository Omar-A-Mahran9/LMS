<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $appends = ['title', 'description', 'full_score']; // add full_score
    protected $casts   = [
        'created_at' => 'date:Y-m-d',
        'updated_at' => 'date:Y-m-d',
    ];


    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en;
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }


    public function course()
    {
        return $this->belongsTo(Course::class);
    }
     public function class()
    {
        return $this->belongsTo(CourseClass::class);
    }

        public function section()
        {
            return $this->belongsTo(Section::class);
        }

    // public function section()
    // {
    //     return $this->belongsTo(CourseSection::class, 'course_section_id');
    // }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

        public function getFullScoreAttribute()
    {
        return $this->questions->sum('points');
    }

    // Minimum % of questions a student must answer (and submit) before a quiz-required class unlocks
    public const REQUIRED_ANSWERED_PERCENT = 75;

    /**
     * Where the student stands with this quiz:
     *  - passed:          a submitted attempt answered >= REQUIRED_ANSWERED_PERCENT
     *  - open_attempt:    an unfinished attempt the student should continue (null if none)
     *  - limit_reached:   no attempts left and nothing open to continue
     */
    public function accessStatusFor($studentId): array
    {
        $status = ['passed' => false, 'open_attempt' => null, 'limit_reached' => false];
        if (!$studentId) {
            return $status;
        }

        $attempts = $this->attempts()->where('student_id', $studentId)->get();

        // Time ran out on an open attempt: grade what was saved so it counts as a normal submission
        foreach ($attempts as $attempt) {
            if (!$attempt->isSubmitted() && $attempt->isExpired()) {
                $attempt->setRelation('quiz', $this);
                $attempt->finalize();
            }
        }

        $status['passed'] = $attempts->contains(fn ($a) => $a->meetsRequiredPercent());
        $status['open_attempt'] = $attempts->first(fn ($a) => !$a->isSubmitted());
        $status['limit_reached'] = !$status['open_attempt']
            && $this->attempt_count !== null
            && $attempts->count() >= $this->attempt_count;

        return $status;
    }

}
