<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{

public function toArray(Request $request): array
{
    $studentId = auth('api')->id();

    $attemptsMade = $this->attempts()
        ->where('student_id', $studentId)
        ->count();

    // Load questions with readingPassage
    $questions = $this->relationLoaded('questions')
        ? $this->questions
        : $this->questions()->with('readingPassage')->get();

    $questions = $questions->sortBy('id')->values(); // Keep original order

    $finalQuestions = [];
    $handledPassages = [];

    foreach ($questions as $question) {
        if ($question->reading_passage_id) {
            // If this passage was already handled, skip
            if (in_array($question->reading_passage_id, $handledPassages)) {
                continue;
            }

            // Get all questions related to this passage
            $relatedQuestions = $questions->where('reading_passage_id', $question->reading_passage_id);

            $finalQuestions[] = [
                'type' => 'reading_passage',
                'id' => $question->reading_passage_id,
                'description' => app()->getLocale() === 'ar'
                    ? $question->readingPassage?->description_ar
                    : $question->readingPassage?->description_en,
                'questions' => QuestionResource::collection($relatedQuestions)->values(),
            ];

            // Mark this passage as handled
            $handledPassages[] = $question->reading_passage_id;
        } else {
            // Normal question
            $finalQuestions[] = (new QuestionResource($question))->toArray($request);
        }
    }

    return [
        "id" => $this->id,
        "title" => $this->title,
        "description" => $this->description,

        "course_id" => $this->course?$this->course->id:null,
        "class_id" => $this->class?$this->class->id:null,

        'have_duration' => (bool) $this->duration_minutes,
        'duration_minutes' => $this->duration_minutes,
        'attempt_count' => $this->attempt_count,
        'remaining_attempts' => $this->attempt_count !== null
            ? max(0, $this->attempt_count - $attemptsMade)
            : null,
        'full_score' => $questions->sum('points'),
        'question_count' => $questions->count(),
        'questions' => $finalQuestions,
    ];
}

}
