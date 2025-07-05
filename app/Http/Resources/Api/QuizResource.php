<?php

namespace App\Http\Resources\Api;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // public function toArray(Request $request): array
    // {

    //      // Get current authenticated student (assuming you're using sanctum or similar)
    //     $studentId = auth('api')->id(); // or however you auth students

    //     // Count the number of attempts made by this student
    //     $attemptsMade = $this->attempts()
    //         ->where('student_id', $studentId)
    //         ->count();
    //     $questions = $this->relationLoaded('questions') ? $this->questions : $this->questions()->get();

    //     return [
    //         "id" => $this->id,
    //         'have_duration'=> $this->duration_minutes?true:false,
    //         'duration_minutes' => $this->duration_minutes,
    //         'attempt_count' => $this->attempt_count,
    //          'remaining_attempts' => $this->attempt_count !== null
    //             ? max(0, $this->attempt_count - $attemptsMade)
    //             : null, // null if unlimited
    //         'full_score' => $questions->sum('points'),
    //         'question_count' => $questions->count(),

    //         'questions' => $this->whenLoaded('questions')
    //         ? QuestionResource::collection($this->questions)
    //         : [],        ];
    // }

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
