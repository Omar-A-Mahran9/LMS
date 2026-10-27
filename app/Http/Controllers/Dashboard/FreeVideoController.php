<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreFreeVideoRequest;
use App\Http\Requests\Dashboard\UpdateFreeVideoRequest;
use App\Models\Category;
use App\Models\FreeVideo;
use Illuminate\Http\Request;

class FreeVideoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_free_videos');

        if ($request->ajax()) {
            return response()->json(getModelData(model: new FreeVideo(), relations: ['category' => ['id', 'name_ar', 'name_en']]));
        }

        $categories = Category::withoutGlobalScope('onlyParents')->select('id', 'name_ar', 'name_en', 'parent_id')->get();

        return view('dashboard.free-videos.index', compact('categories'));
    }

    public function store(StoreFreeVideoRequest $request)
    {
        $this->authorize('create_free_videos');

        $data = $this->prepareData($request, $request->validated());

        if ($request->hasFile('image')) {
            $data['image'] = uploadImageToDirectory($request->file('image'), 'FreeVideos');
        }

        $freeVideo = FreeVideo::create($data);

        return response()->json([
            'message' => __('Free video created successfully'),
            'data' => $freeVideo,
        ]);
    }

    public function update(UpdateFreeVideoRequest $request, FreeVideo $freeVideo)
    {
        $this->authorize('update_free_videos');

        $data = $this->prepareData($request, $request->validated());

        if ($request->hasFile('image')) {
            deleteImageFromDirectory($freeVideo->image, 'FreeVideos');
            $data['image'] = uploadImageToDirectory($request->file('image'), 'FreeVideos');
        }

        $freeVideo->update($data);

        return response()->json([
            'message' => __('Free video updated successfully'),
            'data' => $freeVideo,
        ]);
    }

    public function destroy(FreeVideo $freeVideo)
    {
        $this->authorize('delete_free_videos');

        if ($freeVideo->image) {
            deleteImageFromDirectory($freeVideo->image, 'FreeVideos');
        }
        $freeVideo->delete();

        return response()->json([
            'status' => true,
            'message' => __('Free video deleted successfully.'),
        ]);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_free_videos');

        $videos = FreeVideo::whereIn('id', (array) $request->selected_items_ids)->get();
        foreach ($videos as $video) {
            if ($video->image) {
                deleteImageFromDirectory($video->image, 'FreeVideos');
            }
            $video->delete();
        }

        return response(["selected free videos deleted successfully"]);
    }

    /** Flags from the switches, and the duration entered in minutes. */
    private function prepareData(Request $request, array $data): array
    {
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['category_id'] = $data['category_id'] ?? null;

        $data['duration_seconds'] = isset($data['duration_minutes']) ? $data['duration_minutes'] * 60 : null;
        unset($data['duration_minutes']);

        return $data;
    }
}
