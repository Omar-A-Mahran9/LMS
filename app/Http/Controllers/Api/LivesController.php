<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LiveResource;
use App\Http\Resources\Api\LivesResource;
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

        return $this->success('',LivesResource::collection($lives));
    }

  public function show($id)
    {
        $live = Live::where('is_active', true)->findOrFail($id);
        return $this->success('', new LiveResource($live));
    }

}
