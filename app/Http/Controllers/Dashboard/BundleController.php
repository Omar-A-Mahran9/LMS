<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleAccessCode;
use App\Models\CourseClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_bundle');

        if ($request->ajax()) {

            return response()->json(
                getModelData(
                    model: new Bundle(),
                    relations: [
    'classes' => ['id', 'title_ar', 'title_en'],
    'codes' => ['id', 'code']
]
                )
            );
        }

        $classes = CourseClass::where('is_active', 1)->get();

        return view('dashboard.bundles.index', compact('classes'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'title_ar'         => 'required|string|max:255',
            'title_en'         => 'required|string|max:255',

            'description_ar'   => 'required|string',
            'description_en'   => 'required|string',

            'image'            => 'required|image',

            'classes'          => 'required|array|min:1',
            'classes.*'        => 'exists:classes,id',

            'price'            => 'required|numeric|min:0',
            'discount_price'   => 'nullable|numeric|min:0|lte:price',

            'starts_at'        => 'nullable|date',
            'expires_at'       => 'nullable|date|after_or_equal:starts_at',

            'code_count'       => 'required|integer|min:1',
            'usage_limit'      => 'required|integer|min:1',

            'single_use'       => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Upload Image
            |--------------------------------------------------------------------------
            */

            $image = null;

            if ($request->hasFile('image')) {
                $image = uploadImageToDirectory(
                    $request->file('image'),
                    'bundles'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Bundle
            |--------------------------------------------------------------------------
            */

            $bundle = Bundle::create([
                'title_ar'        => $request->title_ar,
                'title_en'        => $request->title_en,
                'description_ar'  => $request->description_ar,
                'description_en'  => $request->description_en,
                'image'           => $image,
                'price'           => $request->price,
                'discount_price'  => $request->discount_price,
                'is_active'       => $request->boolean('is_active'),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach Classes
            |--------------------------------------------------------------------------
            */

            $bundle->classes()->sync($request->classes);

            /*
            |--------------------------------------------------------------------------
            | Generate Access Codes
            |--------------------------------------------------------------------------
            */

            $count = $request->code_count;

            for ($i = 0; $i < $count; $i++) {

                do {
                    $generatedCode = strtoupper(Str::random(10));
                } while (
                    BundleAccessCode::where('code', $generatedCode)->exists()
                );

                BundleAccessCode::create([
                    'bundle_id'    => $bundle->id,
                    'code'         => $generatedCode,
                    'single_use'   => $request->boolean('single_use'),
                    'usage_limit'  => $request->usage_limit,
                    'used_count'   => 0,
                    'starts_at'    => $request->starts_at,
                    'expires_at'   => $request->expires_at,
                    'is_active'    => $request->boolean('is_active'),
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => __('Bundle created successfully'),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Bundle $bundle)
    {
        $request->validate([

            'title_ar' => 'required|string|max:255',

            'title_en' => 'required|string|max:255',

            'description_ar' => 'required|string',

            'description_en' => 'required|string',

            'image' => 'nullable|image',

            'classes' => 'required|array|min:1',

            'classes.*' => 'exists:classes,id',

            'usage_limit' => 'nullable|integer|min:1',

            'is_active' => 'nullable|boolean',

            'single_use' => 'nullable|boolean',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Upload Image
        |--------------------------------------------------------------------------
        */

        $image = $bundle->image;

        if ($request->hasFile('image')) {

            $image = uploadImageToDirectory(
                $request->file('image'),
                'bundles'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Update Bundle
        |--------------------------------------------------------------------------
        */

        $bundle->update([

            'title_ar' => $request->title_ar,

            'title_en' => $request->title_en,

            'description_ar' => $request->description_ar,

            'description_en' => $request->description_en,

            'image' => $image,

            'is_active' => $request->boolean('is_active'),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Sync Classes
        |--------------------------------------------------------------------------
        */

        $bundle->classes()->sync(
            $request->classes
        );


        /*
        |--------------------------------------------------------------------------
        | Update Codes
        |--------------------------------------------------------------------------
        */

        $bundle->codes()->update([

            'is_active' => $request->boolean('is_active'),

            'single_use' => $request->boolean('single_use'),

            'usage_limit' => $request->usage_limit,
        ]);


        return response()->json([

            'status' => true,

            'message' => __('Bundle updated successfully'),

            'data' => $bundle->load([
                'classes',
                'codes'
            ]),
        ]);
    }

    public function exportPDF(Request $request)
    {
        $classId = $request->get('class_id');

        $query = ClassAccessCode::with('class')->where('is_active', 1);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        $codes = $query->get();

        $pdf = Pdf::loadView('dashboard.codes.code', compact('codes'));

        return $pdf->download('access_codes_report.pdf');
    }
    public function destroy(ClassAccessCode $generateCode)
    {
        $generateCode->delete();

        return response()->json(['status' => true, 'message' => 'تم حذف الكود بنجاح.']);
    }


    public function show(ClassAccessCode $generateCode)
    {
        $generateCode->load(['class', 'logs.student']);

        return view('dashboard.codes.show', [
            'code' => $generateCode
        ]);
    }

}
