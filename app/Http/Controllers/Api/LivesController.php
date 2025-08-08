<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Live;
use Illuminate\Http\Request;

class LivesController extends Controller
{

    public function index(Request $request)
    {
        $courseId = $request->get('course_id');
        $classId = $request->get('class_id');

        $query = Live::query()->where('is_active', true);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $lives = $query->latest()->get();

        return $this->success('',$lives);
    }

}
