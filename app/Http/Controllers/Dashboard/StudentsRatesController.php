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
         $data = getModelData(model: new Student_rate(), relations: ['customer' => ['id', 'full_name']]);

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
            'image'       => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:512',
            'rate'        => 'required|numeric|min:1|max:5',
            'status'      => 'required|in:pending,reject,approve',
            'category_id' => 'required|exists:categories,id',
            'text'        => 'required_without:audio|string|max:2000',
             'audio'       => 'required_without:text|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg|max:5120',

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

        return response(["message" => "Rate submitted successfully"]);
    }


    public function update(Request $request, Student_rate $Student_rate)
    {
        $this->authorize('update_students_rate');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:512',
            'rate'      => 'required|numeric|min:1|max:5',
            'status'    => 'required|in:pending,reject,approve',
            'category_id' => 'required|exists:categories,id',
            'text'        => 'required_without:audio|string|max:2000',
            'audio'       => 'required_without:text|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg|max:5120',

        ]);

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

        return response(["message" => "Rate updated successfully"]);
    }




    public function destroy( $students_rates)
    {
         $customrRate=Student_rate::find($students_rates);
        $this->authorize('delete_students_rate');

        $customrRate->delete();
        return response(["students_rates deleted successfully"]);
    }
}
