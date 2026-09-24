<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassAccessCode;
use App\Models\CourseClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenerateCodeController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('view_generateCode');

        if ($request->ajax()) {
            // Return JSON data for AJAX requests
            return response()->json(getModelData(model: new ClassAccessCode(),relations: ['class' => ['id', 'title_ar','title_en' ]]));
        } else {
            $classes = CourseClass::where('is_active', 1)->get();
            // Return the main view with data
            return view('dashboard.codes.index',compact('classes'));
        }
    }



public function store(Request $request)
{
    $this->authorize('create_generateCode');

    $request->validate([
        'class_id'    => 'required|exists:classes,id',
        'code'        => 'nullable|string|unique:class_access_codes,code',
        'usage_limit' => 'nullable|integer|min:1',
        'code_count'  => 'nullable|integer|min:1',
    ]);

    $codes = [];

    if ($request->filled('code')) {
        // حالة كود واحد مخصص من المستخدم
        $accessCode = ClassAccessCode::create([
            'class_id'    => $request->input('class_id'),
            'code'        => $request->input('code'),
            'is_active'   => $request->boolean('is_active'),
            'single_use'  => $request->boolean('single_use'),
            'usage_limit' => $request->input('usage_limit'),
        ]);

        $codes[] = $accessCode;

    } else {
        // حالة توليد عدة أكواد تلقائيًا
        $count = $request->input('code_count') ?? 1;

        for ($i = 0; $i < $count; $i++) {
            $generatedCode = strtoupper(Str::random(10));

            // التأكد من أن الكود غير مكرر
            while (ClassAccessCode::where('code', $generatedCode)->exists()) {
                $generatedCode = strtoupper(Str::random(10));
            }

            $codes[] = ClassAccessCode::create([
                'class_id'    => $request->input('class_id'),
                'code'        => $generatedCode,
                'is_active'   => $request->boolean('is_active'),
                'single_use'  => $request->boolean('single_use'),
                'usage_limit' => $request->input('usage_limit'),
            ]);
        }
    }

    return response()->json([
        'status'  => true,
        'message' => __('Codes generated successfully'),
        'count'   => count($codes),
    ]);
}
public function exportPDF(Request $request)
{
    $this->authorize('view_generateCode');

    $classId = $request->get('class_id');

    $query = ClassAccessCode::with('class')->where('is_active', 1);

    if ($classId) {
        $query->where('class_id', $classId);
    }

    // A PDF of thousands of codes runs the server out of memory: ask for a class first
    if (!$classId && $query->count() > 1000) {
        return response(
            '<div dir="rtl" style="font-family:Tahoma,Arial;padding:40px;text-align:center">'
            . '<h3>' . e(__('There are too many codes to export at once. Choose a class first, then export.')) . '</h3>'
            . '<a href="javascript:window.close()">' . e(__('Close')) . '</a></div>',
            422
        );
    }

    $codes = $query->get();

    $pdf = Pdf::loadView('dashboard.codes.code', compact('codes'));

    return $pdf->download('access_codes_report.pdf');
}

public function update(Request $request, ClassAccessCode $generateCode)
{
    $this->authorize('update_generateCode');

    $request->validate([
        'class_id'    => 'required|exists:classes,id',
        'code'        => 'nullable|string|unique:class_access_codes,code,' . $generateCode->id,
        'usage_limit' => 'nullable|integer|min:1',
        'is_active'   => 'nullable|boolean',
        'single_use'  => 'nullable|boolean',
    ]);

    $generateCode->update([
        'class_id'    => $request->input('class_id'),
        'code'        => $request->input('code') ?? $generateCode->code,
        'is_active'   => $request->has('is_active') ? $request->boolean('is_active') : $generateCode->is_active,
        'single_use'  => $request->has('single_use') ? $request->boolean('single_use') : $generateCode->single_use,
        'usage_limit' => $request->input('usage_limit') ?? $generateCode->usage_limit,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'تم تحديث الكود بنجاح.',
        'data'    => $generateCode,
    ]);
}


    public function destroy(ClassAccessCode $generateCode)
    {
        $this->authorize('delete_generateCode');

        $generateCode->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الكود بنجاح.']);
    }


public function show(ClassAccessCode $generateCode)
{
    $this->authorize('show_generateCode');

    $generateCode->load(['class', 'logs.student']);

    return view('dashboard.codes.show', [
        'code' => $generateCode
    ]);
}

}
