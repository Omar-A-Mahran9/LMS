<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LiveResource;
use App\Http\Resources\Api\LivesResource;
use App\Models\Live;
use Illuminate\Http\Request;

class LivesController extends Controller
{
    /**
     * Lives on air now and coming soon (for the home / grade hints). No stream links here.
     * Filters: category_id, course_id, class_id, status=on_air|current (default current), days, limit.
     * A logged-in student without a category filter sees their grade's lives only.
     * mine=1: only the lives of the courses the student is subscribed to (approved), any grade.
     */
    public function index(Request $request)
    {
        $student = auth('api')->user();
        $mine = $request->boolean('mine');
        if ($mine && !$student) {
            return $this->failure(__('Unauthorized'), 401);
        }
        $categoryId = $request->integer('category_id') ?: (!$mine ? ($student?->category_id ?: null) : null);
        $days = min(max($request->integer('days', 7), 1), 60);

        $lives = Live::published()
            ->with(['course.category', 'class'])
            ->when($request->get('status') === 'on_air', fn($q) => $q->onAir(), fn($q) => $q->current())
            ->where('start_time', '<=', now(Live::TIMEZONE)->addDays($days)->format('Y-m-d H:i:s'))
            ->when($categoryId, fn($q) => $q->forCategory($categoryId))
            ->when($mine, fn($q) => $q->whereHas('course.students', fn($s) => $s
                ->where('student_id', $student->id)
                ->where('course_student.status', 'approved')
                ->where('course_student.is_active', 1)))
            ->when($request->integer('course_id'), fn($q, $id) => $q->where('course_id', $id))
            ->when($request->integer('class_id'), fn($q, $id) => $q->where('class_id', $id))
            ->soonestFirst()
            ->take(min(max($request->integer('limit', 10), 1), 50))
            ->get();

        return $this->success('', LivesResource::collection($lives));
    }

    /** Live details with the stream / meeting links: enrolled students only, or everyone for a public live. */
    public function show($id)
    {
        $live = Live::published()->with(['course', 'class'])->findOrFail($id);

        if ($live->is_public) {
            return $this->success('', new LiveResource($live));
        }

        $studentId = auth('api')->id();
        if (!$studentId) {
            return $this->failure(__('Unauthorized'), 401);
        }

        if (!$live->course->isStudentEnrolled($studentId) && !\App\Models\Bundle::grantsClass($studentId, $live->class_id)) {
            return $this->failure(__('Course not found or unauthorized.'));
        }

        return $this->success('', new LiveResource($live));
    }
}
