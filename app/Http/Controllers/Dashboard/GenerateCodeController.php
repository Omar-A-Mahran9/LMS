<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ClassAccessCode;
use App\Models\CourseClass;
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
        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'code' => 'nullable|string|unique:class_access_codes,code',
            'single_use' => 'required|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        }

        ClassAccessCode::create($data);

        return response()->json(['status' => true, 'message' => 'تم إنشاء الكود بنجاح.']);
    }

    public function update(Request $request, ClassAccessCode $generateCode)
    {
        $data = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'code' => 'required|string|unique:class_access_codes,code,' . $generateCode->id,
            'single_use' => 'required|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $generateCode->update($data);

        return response()->json(['status' => true, 'message' => 'تم تحديث الكود بنجاح.']);
    }

    public function destroy(ClassAccessCode $generateCode)
    {
        $generateCode->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الكود بنجاح.']);
    }

    public function show(ClassAccessCode $generateCode)
    {
        return response()->json($generateCode);
    }
}
