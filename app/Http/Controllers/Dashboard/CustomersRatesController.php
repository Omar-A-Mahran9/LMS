<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

use App\Models\Customer;
use App\Models\CustomerRate;
 use Illuminate\Http\Request;

class CustomersRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view_customersRate');

        $customers = Customer::get(); // Get the count of blogs

         $count_customerRate = CustomerRate::count(); // Get the count of blogs
         $visited_site=10000;
         if ($request->ajax()){
         $data = getModelData(model: new CustomerRate(), relations: ['customer' => ['id', 'full_name']]);

            return response($data);
         }
        else
            return view('dashboard.customerRate.index',compact('count_customerRate','visited_site','customers'));
    }


    public function store(Request $request)
    {
        $this->authorize('update_customersRate');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:512',
            'rate'      => 'required|numeric|min:1|max:5',
            'status'    => 'required|in:pending,reject,approve',
            'comment'   => 'required|string',
            'audio'     => 'required|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg|max:5120',
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


        CustomerRate::create($data);

        return response(["message" => "Rate submitted successfully"]);
    }


    public function update(Request $request, CustomerRate $customerRate)
    {
        $this->authorize('update_customersRate');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:512',
            'rate'      => 'required|numeric|min:1|max:5',
            'status'    => 'required|in:pending,reject,approve',
            'comment'   => 'required|string',
            'audio'     => 'nullable|file|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/ogg|max:5120',
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

        $customerRate->update($data);

        return response(["message" => "Rate updated successfully"]);
    }




    public function destroy( $customers_rates)
    {
         $customrRate=CustomerRate::find($customers_rates);
        $this->authorize('delete_customersRate');

        $customrRate->delete();
        return response(["customers_rates deleted successfully"]);
    }
}
