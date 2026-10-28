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
    private const MAX_CODES_PER_PDF = 2000;

    public function index(Request $request)
    {
        $this->authorize('view_bundle');

        if ($request->ajax()) {

            return response()->json(
                getModelData(
                    model: new Bundle(),
                    // qualified columns (the pivot table has an id too); codes need bundle_id to be matched
                    relations: [
                        'classes' => ['classes.id', 'classes.title_ar', 'classes.title_en'],
                        'codes' => ['id', 'bundle_id', 'single_use', 'usage_limit', 'starts_at', 'expires_at'],
                    ]
                )
            );
        }

        // The course name tells apart classes with the same title
        $classes = CourseClass::where('is_active', 1)->with('course:id,title_ar,title_en')->get();

        // The codes export picks one bundle
        $bundles = Bundle::select('id', 'title_ar', 'title_en')->withCount('codes')->latest('id')->get();

        return view('dashboard.bundles.index', compact('classes', 'bundles'));
    }



    public function store(Request $request)
    {
        $this->authorize('create_bundle');

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

            'code_count'       => 'required|integer|min:1|max:1000',
            'usage_limit'      => 'nullable|integer|min:1',

            'single_use'       => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
            'requires_login'   => 'nullable|boolean',
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
                'requires_login'  => $request->boolean('requires_login', true),
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

            $this->generateCodes($bundle, (int) $request->code_count, $request);

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
        $this->authorize('update_bundle');

        $request->validate([

            'title_ar' => 'required|string|max:255',

            'title_en' => 'required|string|max:255',

            'description_ar' => 'required|string',

            'description_en' => 'required|string',

            'image' => 'nullable|image',

            'classes' => 'required|array|min:1',

            'classes.*' => 'exists:classes,id',

            'price' => 'required|numeric|min:0',

            'discount_price' => 'nullable|numeric|min:0|lte:price',

            'starts_at' => 'nullable|date',

            'expires_at' => 'nullable|date|after_or_equal:starts_at',

            // more codes for the bundle (the generated ones are kept)
            'add_codes' => 'nullable|integer|min:0|max:1000',

            'usage_limit' => 'nullable|integer|min:1',

            'is_active' => 'nullable|boolean',

            'single_use' => 'nullable|boolean',

            'requires_login' => 'nullable|boolean',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Upload Image
        |--------------------------------------------------------------------------
        */

        $image = $bundle->image;

        if ($request->hasFile('image')) {

            if ($bundle->image) {
                deleteImageFromDirectory($bundle->image, 'bundles');
            }

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

            'price' => $request->price,

            'discount_price' => $request->discount_price,

            'is_active' => $request->boolean('is_active'),

            'requires_login' => $request->boolean('requires_login', true),
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

            'usage_limit' => $request->boolean('single_use') ? null : $request->usage_limit,

            'starts_at' => $request->starts_at,

            'expires_at' => $request->expires_at,
        ]);

        if ($request->integer('add_codes') > 0) {
            $this->generateCodes($bundle, $request->integer('add_codes'), $request);
        }


        return response()->json([

            'status' => true,

            'message' => __('Bundle updated successfully'),

            'data' => $bundle->load([
                'classes',
                'codes'
            ]),
        ]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_bundle');

        $bundles = Bundle::whereIn('id', (array) $request->selected_items_ids)->get();
        foreach ($bundles as $bundle) {
            if ($bundle->image) {
                deleteImageFromDirectory($bundle->image, 'bundles');
            }
            $bundle->delete();
        }

        return response(["selected bundles deleted successfully"]);
    }

    private function generateCodes(Bundle $bundle, int $count, Request $request): void
    {
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
                'usage_limit'  => $request->boolean('single_use') ? null : $request->usage_limit,
                'used_count'   => 0,
                'starts_at'    => $request->starts_at,
                'expires_at'   => $request->expires_at,
                'is_active'    => $request->boolean('is_active'),
            ]);
        }
    }

    /**
     * Codes PDF: one bundle (bundle_id) or all of them, as a table or as cards to cut and hand out
     * (layout=cards), all the codes or only the ones still usable (status=available).
     */
    public function exportPDF(Request $request)
    {
        $this->authorize('view_bundle');

        // The PDF is handed to students: always Arabic, whatever the dashboard language
        app()->setLocale('ar');

        $bundle = $request->filled('bundle_id') ? Bundle::findOrFail($request->integer('bundle_id')) : null;
        $onlyAvailable = $request->get('status') === 'available';

        $query = BundleAccessCode::with('bundle:id,title_ar,title_en')
            ->when($bundle, fn ($q) => $q->where('bundle_id', $bundle->id));

        // Thousands of rows run dompdf out of memory
        if (($total = $query->count()) > self::MAX_CODES_PER_PDF) {
            return response(
                '<div dir="rtl" style="font-family:Tahoma,Arial;padding:40px;text-align:center">'
                . '<h3>' . e(__('There are too many codes to export at once (:count). Export the codes of one bundle.', ['count' => $total])) . '</h3>'
                . '<a href="javascript:window.close()">' . e(__('Close')) . '</a></div>',
                422
            );
        }

        $codes = $query->orderBy('bundle_id')->orderBy('id')->get()
            ->when($onlyAvailable, fn ($codes) => $codes->filter->canBeUsed()->values());

        $filters = array_filter([
            'الباقة' => $bundle?->title_ar ?? 'كل الباقات',
            'الأكواد' => $onlyAvailable ? 'المتاحة للاستخدام فقط' : null,
        ]);

        $pdf = Pdf::loadView('dashboard.codes.code', [
            'codes'   => $codes,
            'layout'  => $request->get('layout') === 'cards' ? 'cards' : 'table',
            'title'   => $bundle ? 'أكواد باقة: ' . $bundle->title_ar : 'أكواد الباقات',
            'filters' => $filters,
            // Students activate a bundle code on the bundle's page
            'url'     => rtrim(config('app.website_url'), '/') . '/bundles' . ($bundle ? "/{$bundle->id}" : ''),
        ])->setPaper('a4', 'portrait');

        return $pdf->download(($bundle ? "bundle_{$bundle->id}_codes_" : 'bundles_codes_') . now()->format('Y-m-d_His') . '.pdf');
    }

    public function destroy(Bundle $bundle)
    {
        $this->authorize('delete_bundle');

        // codes and class links are removed by the foreign keys (cascade)
        if ($bundle->image) {
            deleteImageFromDirectory($bundle->image, 'bundles');
        }

        $bundle->delete();

        return response()->json(['status' => true, 'message' => __('Bundle deleted successfully')]);
    }

    public function show(Bundle $bundle)
    {
        $this->authorize('show_bundle');

        $bundle->load([
            'classes',
            'activations.student',
            'codes' => function ($query) {
                $query->withCount('logs')->latest();
            },
        ]);

        // إحصائيات سريعة عن الأكواد
        $stats = [
            'total'    => $bundle->codes->count(),
            'active'   => $bundle->codes->where('is_active', true)->count(),
            'inactive' => $bundle->codes->where('is_active', false)->count(),
            'used_up'  => $bundle->codes->filter(fn ($c) => !$c->canBeUsed())->count(),
            'available' => $bundle->codes->filter(fn ($c) => $c->canBeUsed())->count(),
        ];

        return view('dashboard.bundles.show', [
            'bundle' => $bundle,
            'stats'  => $stats,
        ]);
    }

    public function showCode(BundleAccessCode $generateCode)
    {
        $this->authorize('show_bundle');

        $generateCode->load(['bundle', 'logs.student']);
        return view('dashboard.codes.show', ['code' => $generateCode]);
    }

}
