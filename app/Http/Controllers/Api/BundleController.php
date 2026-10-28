<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BundleResource;
use App\Http\Resources\Api\ClassDetailsResource;
use App\Models\Bundle;
use App\Models\BundleAccessCode;
use App\Models\BundleStudent;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Bundles (packages of classes): the student activates a bundle once with a code,
 * then opens its classes from /lessons/{id} like a subscribed student.
 */
class BundleController extends Controller
{
    private function classesQuery()
    {
        return fn($q) => $q->where('classes.is_active', 1)->with('course.category');
    }

    /**
     * Bundles on sale. Filters: category_id, course_id, search, per_page.
     * mine=1 (logged in): the bundles the student activated, on sale or not.
     */
    public function index(Request $request)
    {
        $studentId = auth('api')->id();
        $mine = $request->boolean('mine');
        if ($mine && !$studentId) {
            return $this->failure(__('Unauthorized'), 401);
        }

        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->integer('category_id');
        $courseId = $request->integer('course_id');

        $bundles = Bundle::query()
            ->when($mine, fn($q) => $q->ownedBy($studentId), fn($q) => $q->where('is_active', 1))
            ->with(['classes' => $this->classesQuery()])
            ->when($categoryId, fn($q) => $q->whereHas('classes.course', fn($c) => $c->where('category_id', $categoryId)))
            ->when($courseId, fn($q) => $q->whereHas('classes', fn($c) => $c->where('course_id', $courseId)))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $q->where(fn($s) => $s->where('title_ar', 'like', $like)->orWhere('title_en', 'like', $like));
            })
            ->latest('id')
            ->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->successWithPagination('', BundleResource::collection($bundles)->response()->getData(true));
    }

    /** Bundle page: details and its classes. Owners see it even after it stopped selling. */
    public function show($id)
    {
        $bundle = Bundle::with(['classes' => $this->classesQuery()])->findOrFail($id);

        if (!$bundle->is_active && !$bundle->isOwnedBy(auth('api')->id())) {
            return $this->failure(__('Bundle not found'), 404);
        }

        return $this->success('', new BundleResource($bundle));
    }

    /** Activate a bundle with one of its codes (logged-in students). The code is used once, here. */
    public function activate(Request $request)
    {
        $request->validate([
            'bundle_id' => 'required|integer',
            'code' => 'required|string|max:100',
        ]);

        $student = auth('api')->user();
        $bundle = Bundle::find($request->bundle_id);

        if (!$bundle) {
            return $this->failure(__('Bundle not found'), 404);
        }

        // Already activated: nothing is used up again
        if ($bundle->isOwnedBy($student->id)) {
            return $this->success(__('You already have this bundle'), [
                'bundle' => new BundleResource($bundle->load(['classes' => $this->classesQuery()])),
            ]);
        }

        if (!$bundle->is_active) {
            return $this->failure(__('This bundle is not available now'));
        }

        $code = BundleAccessCode::where('bundle_id', $bundle->id)
            ->where('code', trim($request->code))
            ->first();

        if (!$code || !$code->is_active) {
            return $this->failure(__('Invalid or inactive code'));
        }

        if ($code->single_use && $code->used_count >= 1) {
            return $this->failure(__('This code has already been used'));
        }

        $activated = DB::transaction(function () use ($code, $bundle, $student, $request) {
            // limits + validity dates re-checked atomically
            if (!$code->consumeUse()) {
                return false;
            }

            BundleStudent::create([
                'bundle_id' => $bundle->id,
                'student_id' => $student->id,
                'access_code_id' => $code->id,
                'device_ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'used_at' => now(),
            ]);

            return true;
        });

        if (!$activated) {
            return $this->failure(__('This access code has expired or reached its usage limit.'));
        }

        return $this->success(__('Bundle activated successfully'), [
            'bundle' => new BundleResource($bundle->load(['classes' => $this->classesQuery()])),
        ]);
    }

    /**
     * A class of a bundle the student activated (kept for older clients: the website opens
     * the class from /lessons/{id}, which accepts bundle owners too). The code is no longer needed.
     */
    public function classVideos($id)
    {
        $studentId = auth('api')->id();

        $class = CourseClass::where('id', $id)->where('is_active', 1)->first();
        if (!$class) {
            return $this->failure(__('Class not found or unpublished'));
        }

        if (!Bundle::grantsClass($studentId, $class->id)) {
            return $this->failure(__('This class does not belong to your bundles.'));
        }

        $cacheKey = "class_viewed_{$class->id}_student_{$studentId}";
        if (!Cache::has($cacheKey)) {
            $class->increment('views');
            Cache::put($cacheKey, true, now()->addHours(6));
        }

        return $this->success(__('Bundle class videos loaded'), [
            'class_data' => new ClassDetailsResource($class, $studentId),
        ]);
    }
}
