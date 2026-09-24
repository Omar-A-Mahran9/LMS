<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait SyncsQuestionAnswers
{
    /**
     * Update a question's answers in place (by position) instead of deleting and recreating them,
     * so answers students already picked keep pointing at the same answer rows.
     */
    protected function syncQuestionAnswers($question, Request $request): void
    {
        $rows = [];

        if ($request->type === 'multiple_choice') {
            foreach ($request->answers ?? [] as $answerData) {
                $rows[] = [
                    'answer_ar'  => $answerData['text_ar'] ?? '',
                    'answer_en'  => $answerData['text_en'] ?? '',
                    'is_correct' => !empty($answerData['is_correct']),
                ];
            }
        } elseif ($request->type === 'true_false') {
            $correct = $request->correct_tf === 'true';
            $rows = [
                ['answer_ar' => 'صحيح', 'answer_en' => 'True',  'is_correct' => $correct],
                ['answer_ar' => 'خطأ',  'answer_en' => 'False', 'is_correct' => !$correct],
            ];
        }

        $existing = $question->answers()->orderBy('id')->get()->values();

        foreach ($rows as $index => $row) {
            if ($answer = $existing->get($index)) {
                $answer->update($row);
            } else {
                $question->answers()->create($row);
            }
        }

        $existing->slice(count($rows))->each->delete();

        $question->expected_answer = $request->type === 'short_answer' ? $request->short_answer : null;
        $question->save();
    }
}
