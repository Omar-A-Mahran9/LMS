<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreGovernmentsRequest;
use App\Http\Requests\Dashboard\UpdateCityRequest;
use App\Http\Requests\Dashboard\UpdateGovernmentsRequest;
use App\Models\City;
use App\Models\Government;
use Illuminate\Http\Request;

class GovernmentsController extends Controller
{
    public function index(Request $request)
    {

        $this->authorize('view_governments');

        if ($request->ajax())
            return response(getModelData(model: new Government()));
        else
            return view('dashboard.governments.index' );
    }

    public function store(StoreGovernmentsRequest $request)
    {
        $data = $request->validated();
        Government::create($data);

        return response(["government created successfully"]);
    }

    public function update(UpdateGovernmentsRequest $request, Government $government)
    {
        $data = $request->validated();
        $government->update($data);

        return response(["government updated successfully"]);
    }

    public function destroy(Government $government)
    {
        $this->authorize('delete_governments');

        $government->delete();

        return response(["government deleted successfully"]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_governments');

        Government::whereIn('id', $request->selected_items_ids)->delete();

        return response(["selected governments deleted successfully"]);
    }
    public function restoreSelected(Request $request)
    {
        $this->authorize('delete_governments');

        Government::withTrashed()->whereIn('id', $request->selected_items_ids)->restore();

        return response(["selected governments restored successfully"]);
    }
}
