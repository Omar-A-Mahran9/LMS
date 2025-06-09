<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{

public function index(Request $request)
{
    $this->authorize('view_enrollments'); // or a more specific ability like 'view_enrollments'

    if ($request->ajax()) {
        // Return JSON for DataTable or AJAX listing
        return response()->json(
            getModelData(
                model: new Enrollment(),
                relations: [
                    'student' => ['id', 'first_name', 'last_name'],
                    'course' => ['id', 'title_ar', 'title_en']
                ]
            )
        );
    } else {
        // Return the Blade view
        return view('dashboard.enrollments.index');
    }
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
