<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Slider;
use App\Models\NewsLetter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreSliderRequest;
use App\Http\Requests\Dashboard\UpdateSliderRequest;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_sliders');

        if ($request->ajax())
        {
            $slider = getModelData(model: new Slider());

            return response()->json($slider);
        }

        return view('dashboard.sliders.index');
    }

    public function show(Slider $slider)
    {
        $this->authorize('show_sliders');
        return view('dashboard.sliders.show', compact('slider'));
    }
    public function create()
    {
        $this->authorize('create_sliders');
        return view('dashboard.sliders.create');
    }



    public function store(StoreSliderRequest $request)
    {
        $this->authorize('create_sliders');

        $data = $request->validated();
        $data['status'] = $request->has('status') ? $request->status : 0;
        $data['is_video'] = $request->has('is_video') ? $request->boolean('is_video') : false;
         // Handle background image only if it's not a video
        if (!$data['is_video']) {
            $data['background'] = uploadImageToDirectory($request->file('background'), "Sliders");
            $data['video_url'] = null; // Clear any video URL if not a video
        } else {
            $data['background'] = ''; // Optional: or store a placeholder
        }

        Slider::create($data);

        return response(["slider created successfully"]);
    }


    public function edit(Slider $slider)
    {
        $this->authorize('update_sliders');

        return view('dashboard.sliders.edit', compact('slider'));
    }


    public function update(UpdateSliderRequest $request, Slider $slider)
    {
        $this->authorize('update_sliders');

        $data = $request->validated();
        $data['status'] = $request->has('status') ? $request->status : 0;
        $data['is_video'] = $request->has('is_video') ? $request->boolean('is_video') : false;

        if ($data['is_video']) {
            // If it's a video, optionally remove the background image
            $data['background'] = $slider->background; // Keep existing background or set to null
            $data['video_url'] = $request->input('video_url'); // Already validated
        } else {
            // It's an image slider, handle background update if a new image was uploaded
            if ($request->hasFile('background')) {
                deleteImageFromDirectory($slider->background, 'Sliders');
                $data['background'] = uploadImageToDirectory($request->file('background'), "Sliders");
            } else {
                $data['background'] = $slider->background; // Retain existing if no new image
            }

            $data['video_url'] = null; // Clear video URL
        }

        $slider->update($data);

        return response(["Slider updated successfully"]);
    }

    public function destroy(Slider $slider)
    {
        $this->authorize('delete_sliders');

        $slider->delete();

        return response(["Slider deleted successfully"]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_sliders');

        Slider::whereIn('id', $request->selected_items_ids)->delete();

        return response(["selected slider deleted successfully"]);
    }
}
