<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentNotificationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_notifications');

        if ($request->ajax()) {
            $query = StudentNotification::with(['categories:id,name_ar,name_en', 'sender:id,name'])
                ->withCount(['students', 'readers']);

            $total = (clone $query)->count();

            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('title_ar', 'like', "%{$search}%")
                        ->orWhere('title_en', 'like', "%{$search}%")
                        ->orWhere('body_ar', 'like', "%{$search}%");
                });
            }

            if ($type = $request->input('filter_type')) {
                if ($type !== 'all') {
                    $query->where('type', $type);
                }
            }

            $filtered = (clone $query)->count();

            return response()->json([
                'recordsTotal'    => $total,
                'recordsFiltered' => $filtered,
                'data'            => $query->latest('id')
                    ->skip((int) $request->input('start', 0))
                    ->take((int) $request->input('length', 10))
                    ->get(),
            ]);
        }

        $categories = Category::where('is_publish', 1)->get();

        return view('dashboard.notifications.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_notifications');

        $data = $request->validate([
            'target'         => ['required', Rule::in([StudentNotification::TARGET_ALL, StudentNotification::TARGET_CATEGORIES, StudentNotification::TARGET_STUDENTS])],
            'category_ids'   => ['required_if:target,categories', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'student_ids'    => ['required_if:target,students', 'array'],
            'student_ids.*'  => ['integer', 'exists:students,id'],
            'title_ar'       => ['required', 'string', 'max:255'],
            'title_en'       => ['nullable', 'string', 'max:255'],
            'body_ar'        => ['required', 'string', 'max:2000'],
            'body_en'        => ['nullable', 'string', 'max:2000'],
            'link'           => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/)/'],
        ]);

        $ids = match ($data['target']) {
            StudentNotification::TARGET_CATEGORIES => $data['category_ids'] ?? [],
            StudentNotification::TARGET_STUDENTS   => $data['student_ids'] ?? [],
            default                                => [],
        };

        StudentNotifier::send([
            'type'     => StudentNotification::TYPE_MESSAGE,
            'title_ar' => $data['title_ar'],
            'title_en' => $data['title_en'] ?? null,
            'body_ar'  => $data['body_ar'],
            'body_en'  => $data['body_en'] ?? null,
            'link'     => $data['link'] ?? null,
            'sent_by'  => auth('admin')->id(),
        ], $data['target'], $ids);

        return response()->json(['message' => __('Notification sent successfully')]);
    }

    public function destroy(StudentNotification $notification)
    {
        $this->authorize('delete_notifications');

        $notification->delete();

        return response()->json(['message' => __('Notification deleted successfully')]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_notifications');

        StudentNotification::whereIn('id', (array) $request->selected_items_ids)->delete();

        return response(['selected notifications deleted successfully']);
    }

    /** Student search for the "specific students" picker (select2). */
    public function searchStudents(Request $request)
    {
        $this->authorize('create_notifications');

        $term = trim((string) $request->get('q'));

        $students = Student::query()
            ->when($term !== '', function ($q) use ($term) {
                $q->where(function ($q) use ($term) {
                    $q->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('id', $term);
                });
            })
            ->limit(20)
            ->get(['id', 'first_name', 'last_name', 'phone']);

        return response()->json([
            'results' => $students->map(fn($s) => [
                'id'   => $s->id,
                'text' => trim("{$s->first_name} {$s->last_name}") . " - {$s->phone}",
            ]),
        ]);
    }
}
