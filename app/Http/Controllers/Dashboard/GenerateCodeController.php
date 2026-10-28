<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassAccessCode;
use App\Models\CourseClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GenerateCodeController extends Controller
{
    // No 0/O or 1/I/L: students type these codes by hand
    private const CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 10;

    private const MAX_CODES_PER_BATCH = 1000;
    private const MAX_CODES_PER_PDF = 2000;

    public const CODES_URL = 'https://mohamed-elnagar.com/classes-by-code';

    private const STATUS_LABELS = [
        'unused'    => 'Unused',
        'used'      => 'Partially used',
        'exhausted' => 'Exhausted',
        'inactive'  => 'Inactive',
    ];

    public function index(Request $request)
    {
        $this->authorize('view_generateCode');

        if ($request->ajax()) {
            return response()->json($this->tableData($request));
        }

        // The course name tells apart classes that share a title across grades
        $classes = CourseClass::with('course:id,title_ar,title_en')
            ->where('is_active', 1)
            ->orderBy('course_id')
            ->get(['id', 'title_ar', 'title_en', 'course_id']);

        return view('dashboard.codes.index', compact('classes'));
    }

    public function store(Request $request)
    {
        $this->authorize('create_generateCode');

        $request->validate([
            'class_id'    => 'required|exists:classes,id',
            'code'        => 'nullable|string|max:50|alpha_dash|unique:class_access_codes,code',
            'usage_limit' => 'nullable|integer|min:1',
            'code_count'  => 'nullable|integer|min:1|max:' . self::MAX_CODES_PER_BATCH,
        ]);

        $singleUse = $request->boolean('single_use');

        $attributes = [
            'class_id'    => $request->class_id,
            'is_active'   => $request->boolean('is_active'),
            'single_use'  => $singleUse,
            // a single-use code is limited to one use anyway
            'usage_limit' => $singleUse ? null : $request->usage_limit,
            'used_count'  => 0,
        ];

        if ($request->filled('code')) {
            ClassAccessCode::create($attributes + ['code' => $request->code]);

            return response()->json([
                'status'  => true,
                'message' => __('Code saved successfully'),
                'count'   => 1,
            ]);
        }

        // One timestamp for the whole batch, so it can be exported on its own right after
        $batchAt = now()->startOfSecond();
        $codes = $this->uniqueCodes((int) ($request->code_count ?: 1));

        DB::transaction(function () use ($codes, $attributes, $batchAt) {
            $rows = array_map(fn ($code) => $attributes + [
                'code'       => $code,
                'created_at' => $batchAt,
                'updated_at' => $batchAt,
            ], $codes);

            foreach (array_chunk($rows, 500) as $chunk) {
                ClassAccessCode::insert($chunk);
            }
        });

        return response()->json([
            'status'     => true,
            'message'    => __('Codes generated successfully'),
            'count'      => count($codes),
            'export_url' => route('dashboard.generateCode.exportPDF', [
                'class_id' => $request->class_id,
                'batch_at' => $batchAt->toDateTimeString(),
            ]),
        ]);
    }

    public function update(Request $request, ClassAccessCode $generateCode)
    {
        $this->authorize('update_generateCode');

        $request->validate([
            'class_id'    => 'required|exists:classes,id',
            'code'        => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('class_access_codes', 'code')->ignore($generateCode->id)],
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        $singleUse = $request->boolean('single_use');

        // The form always sends the switches, so an unchecked one means "off"
        $generateCode->update([
            'class_id'    => $request->class_id,
            'code'        => $request->code,
            'is_active'   => $request->boolean('is_active'),
            'single_use'  => $singleUse,
            'usage_limit' => $singleUse ? null : $request->usage_limit,
        ]);

        return response()->json([
            'status'  => true,
            'message' => __('Code updated successfully'),
            'data'    => $generateCode,
        ]);
    }

    public function show(ClassAccessCode $generateCode)
    {
        $this->authorize('show_generateCode');

        $generateCode->load([
            'class.course:id,title_ar,title_en',
            'logs' => fn ($q) => $q->latest('used_at'),
            'logs.student:id,first_name,last_name,phone',
        ]);

        return view('dashboard.codes.show', ['code' => $generateCode]);
    }

    public function destroy(ClassAccessCode $generateCode)
    {
        $this->authorize('delete_generateCode');

        $generateCode->delete();

        return response()->json(['status' => true, 'message' => __('Code deleted successfully')]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_generateCode');

        ClassAccessCode::whereIn('id', (array) $request->selected_items_ids)->delete();

        return response()->json(['status' => true, 'message' => __('Codes deleted successfully')]);
    }

    public function exportPDF(Request $request)
    {
        $this->authorize('view_generateCode');

        // The PDF is handed to students: always Arabic, whatever the dashboard language
        app()->setLocale('ar');

        $query = $this->filteredQuery($request)->with('class.course:id,title_ar,title_en');

        // A just-generated batch (see store)
        if ($request->filled('batch_at')) {
            $query->where('created_at', Carbon::parse($request->batch_at));
        }

        $total = $query->count();

        // Thousands of rows run dompdf out of memory: narrow the filters first
        if ($total > self::MAX_CODES_PER_PDF) {
            return response(
                '<div dir="rtl" style="font-family:Tahoma,Arial;padding:40px;text-align:center">'
                . '<h3>' . e(__('There are too many codes to export at once (:count). Narrow the filters, then export.', ['count' => $total])) . '</h3>'
                . '<a href="javascript:window.close()">' . e(__('Close')) . '</a></div>',
                422
            );
        }

        $codes = $query->orderBy('id')->get();
        $layout = $request->get('layout') === 'cards' ? 'cards' : 'table';

        $pdf = Pdf::loadView('dashboard.codes.code', [
            'codes'   => $codes,
            'layout'  => $layout,
            'title'   => __('Access codes report'),
            'filters' => $this->filtersSummary($request),
            'url'     => self::CODES_URL,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('access_codes_' . now()->format('Y-m-d_His') . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /** The filters bar (class / course / status / dates / search), shared by the table and the PDF */
    private function filteredQuery(Request $request, bool $withStatus = true): Builder
    {
        // DataTables sends search[value], the export link sends a plain search
        $search = $request->input('search');
        $search = trim((string) (is_array($search) ? ($search['value'] ?? '') : $search));

        return ClassAccessCode::query()
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('course_id'), fn ($q) => $q->whereHas('class', fn ($q) => $q->where('course_id', $request->course_id)))
            ->when($withStatus && in_array($request->status, ClassAccessCode::STATUSES), fn ($q) => $q->status($request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('code', 'like', "%{$search}%")
                ->orWhereHas('class', fn ($q) => $q->where('title_ar', 'like', "%{$search}%")->orWhere('title_en', 'like', "%{$search}%"))));
    }

    /** DataTables server-side response, plus the counts for the status cards */
    private function tableData(Request $request): array
    {
        $sortable = ['code', 'usage_limit', 'used_count', 'is_active', 'created_at'];
        $orderColumn = $request->input('columns.' . $request->input('order.0.column') . '.data');
        $orderDir = $request->input('order.0.dir') === 'asc' ? 'asc' : 'desc';

        $query = $this->filteredQuery($request)->with('class.course:id,title_ar,title_en');
        $filtered = $query->count();

        in_array($orderColumn, $sortable)
            ? $query->orderBy($orderColumn, $orderDir)
            : $query->latest('id');

        // Counts follow every filter except the status itself, so the cards can switch between statuses
        $base = $this->filteredQuery($request, withStatus: false);
        $stats = ['all' => (clone $base)->count()];
        foreach (ClassAccessCode::STATUSES as $status) {
            $stats[$status] = (clone $base)->status($status)->count();
        }

        // Only what the table shows: a serialized Course runs a query for each of its appended attributes
        $rows = $query->skip((int) $request->start)->take((int) ($request->length ?: 10))->get()
            ->map(fn (ClassAccessCode $code) => $code->only([
                'id', 'code', 'class_id', 'single_use', 'usage_limit', 'used_count', 'is_active', 'status',
            ]) + [
                'created_at' => $code->created_at?->format('Y-m-d'),
                'class'      => $code->class ? [
                    'title'  => $code->class->title,
                    'course' => $code->class->course ? ['title' => $code->class->course->title] : null,
                ] : null,
            ]);

        return [
            'draw'            => (int) $request->draw,
            'recordsTotal'    => $filtered,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
            'stats'           => $stats,
        ];
    }

    /** @return string[] */
    private function uniqueCodes(int $count): array
    {
        $codes = [];

        while (count($codes) < $count) {
            $candidates = [];
            for ($i = count($codes); $i < $count; $i++) {
                $candidates[] = $this->randomCode();
            }
            $candidates = array_unique($candidates);

            $taken = ClassAccessCode::whereIn('code', $candidates)->pluck('code')->all();
            $codes = array_values(array_unique(array_merge($codes, array_diff($candidates, $taken))));
        }

        return $codes;
    }

    private function randomCode(): string
    {
        $max = strlen(self::CODE_ALPHABET) - 1;
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::CODE_ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    /** Human-readable filters for the PDF header */
    private function filtersSummary(Request $request): array
    {
        $summary = [];

        if ($request->filled('class_id') && $class = CourseClass::with('course:id,title_ar,title_en')->find($request->class_id)) {
            $summary[__('Class')] = $class->title . ($class->course ? ' — ' . $class->course->title : '');
        }
        if (in_array($request->status, ClassAccessCode::STATUSES)) {
            $summary[__('Status')] = __(self::STATUS_LABELS[$request->status]);
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $summary[__('Created At')] = trim(($request->date_from ?? '…') . ' → ' . ($request->date_to ?? '…'));
        }

        return $summary;
    }
}
