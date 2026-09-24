<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Student;
 use App\Models\Student_rate;
 use Illuminate\Http\Request;

class StudentsRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view_students_rate');

        $students = Student::get(); // Get the count of blogs
        $categories = Category::all();

         $count_Student_rate = Student_rate::count(); // Get the count of blogs
         $visited_site=10000;
         if ($request->ajax()){
         $data = getModelData(model: new Student_rate(), relations: ['category' => ['id', 'name_ar', 'name_en']]);

            return response($data);
         }
        else
            return view('dashboard.Student_rate.index',compact('count_Student_rate','visited_site','students','categories'));
    }


    public function store(Request $request)
    {
        $this->authorize('update_students_rate');

            $data = $request->validate([
            'full_name'   => 'required|string|max:255',
            'image'       => 'required|image|mimes:jpg,png,jpeg,gif,svg,webp|max:1024',
            'rate'        => 'required|numeric|min:1|max:5',
            'status'      => 'required|in:pending,reject,approve',
            'category_id' => 'required|exists:categories,id',
            'text'        => 'required_without:audio|string|max:2000',
            'audio' => 'required_without:text|file|mimes:mp3,wav,ogg|max:10240',

        ]);

        // Upload image
        if ($request->hasFile('image')) {
            $imageName = uploadImageToDirectory($request->file('image'), 'Customer');
            $data['image'] = $imageName;
        }

        // Upload audio
        if ($request->hasFile('audio')) {
            $audioName = uploadAudioToDirectory($request->file('audio'), 'Customer');
            $data['audio'] = $audioName;
        }


        Student_rate::create($data);

        return response(["message" => __("Rate submitted successfully")]);
    }


    public function update(Request $request, $students_rate)
    {
        // the route parameter is {students_rate}, so implicit binding never matched $Student_rate
        $Student_rate = Student_rate::findOrFail($students_rate);
        $this->authorize('update_students_rate');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:1024',
            'rate'      => 'required|numeric|min:1|max:5',
            'status'    => 'required|in:pending,reject,approve',
            'category_id' => 'required|exists:categories,id',
            'text'        => 'nullable|string|max:2000',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',

        ]);

        // On edit, an already uploaded audio counts: only require text when there's no audio at all
        if (empty($data['text']) && !$request->hasFile('audio') && !$Student_rate->audio) {
            return response()->json([
                'message' => __('The text field is required when audio is not present.'),
                'errors'  => ['text' => [__('The text field is required when audio is not present.')]],
            ], 422);
        }

        // Replace image if uploaded
        if ($request->hasFile('image')) {
            $imageName = uploadImageToDirectory($request->file('image'), 'Customer');
            $data['image'] = $imageName;
        }

        // Replace audio if uploaded
        if ($request->hasFile('audio')) {
            $audioName = uploadAudioToDirectory($request->file('audio'), 'Customer');
            $data['audio'] = $audioName;
        }

        $Student_rate->update($data);

        return response(["message" => __("Rate updated successfully")]);
    }




    public function destroy( $students_rates)
    {
         $customrRate=Student_rate::findOrFail($students_rates);
        $this->authorize('delete_students_rate');

        $customrRate->delete();
        return response(["students_rates deleted successfully"]);
    }
}
