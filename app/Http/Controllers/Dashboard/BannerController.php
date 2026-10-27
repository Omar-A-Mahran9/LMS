<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreBannerRequest;
use App\Http\Requests\Dashboard\UpdateBannerRequest;
use App\Models\Banner;
use App\Models\Category;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view_banners');

        if ($request->ajax()) {
            return response()->json(getModelData(model: new Banner(), relations: ['category' => ['id', 'name_ar', 'name_en']]));
        }

        return view('dashboard.banners.index');
    }

    public function create()
    {
        $this->authorize('create_banners');

        return view('dashboard.banners.create', $this->formData(new Banner([
            'placement' => 'home_top',
            'audience'  => 'all',
            'is_active' => true,
        ])));
    }

    public function store(StoreBannerRequest $request)
    {
        $this->authorize('create_banners');

        $data = $this->prepare($request);
        $data['image'] = $request->hasFile('image') ? uploadImageToDirectory($request->file('image'), 'Banners') : null;
        $data['mobile_image'] = $request->hasFile('mobile_image') ? uploadImageToDirectory($request->file('mobile_image'), 'Banners') : null;

        Banner::create($data);

        return response(['banner created successfully']);
    }

    public function edit(Banner $banner)
    {
        $this->authorize('update_banners');

        return view('dashboard.banners.edit', $this->formData($banner));
    }

    public function update(UpdateBannerRequest $request, Banner $banner)
    {
        $this->authorize('update_banners');

        $data = $this->prepare($request);

        foreach (['image', 'mobile_image'] as $field) {
            if ($request->hasFile($field)) {
                if ($banner->$field) deleteImageFromDirectory($banner->$field, 'Banners');
                $data[$field] = uploadImageToDirectory($request->file($field), 'Banners');
            } elseif ($request->boolean('remove_' . $field) && $banner->$field) {
                deleteImageFromDirectory($banner->$field, 'Banners');
                $data[$field] = null;
            }
        }

        $banner->update($data);

        return response(['banner updated successfully']);
    }

    /** Quick on / off from the list */
    public function toggle(Banner $banner)
    {
        $this->authorize('update_banners');

        $banner->update(['is_active' => !$banner->is_active]);

        return response(['is_active' => $banner->is_active]);
    }

    public function destroy(Banner $banner)
    {
        $this->authorize('delete_banners');

        $this->deleteImages($banner);
        $banner->delete();

        return response(['banner deleted successfully']);
    }

    public function deleteSelected(Request $request)
    {
        $this->authorize('delete_banners');

        Banner::whereIn('id', $request->selected_items_ids ?? [])->get()->each(function (Banner $banner) {
            $this->deleteImages($banner);
            $banner->delete();
        });

        return response(['selected banners deleted successfully']);
    }

    private function prepare(Request $request): array
    {
        $data = collect($request->validated())->except(['image', 'mobile_image'])->all();

        // Grade targeting only makes sense for logged in students
        if ($data['audience'] === 'guests') {
            $data['category_id'] = null;
        }

        return $data;
    }

    private function deleteImages(Banner $banner): void
    {
        foreach (['image', 'mobile_image'] as $field) {
            if ($banner->$field) deleteImageFromDirectory($banner->$field, 'Banners');
        }
    }

    private function formData(Banner $banner): array
    {
        return [
            'banner'     => $banner,
            'placements' => Banner::PLACEMENTS,
            'audiences'  => Banner::AUDIENCES,
            'categories' => Category::whereNull('parent_id')->get(['id', 'name_ar', 'name_en']),
        ];
    }
}
