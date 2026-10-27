<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BannerResource;
use App\Models\Banner;

class BannerController extends Controller
{
    /** Everything this visitor should see right now, grouped by placement */
    public function index()
    {
        $banners = Banner::live()
            ->visibleTo(auth('api')->user())
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        $grouped = collect(array_keys(Banner::PLACEMENTS))
            ->mapWithKeys(fn ($placement) => [
                $placement => BannerResource::collection($banners->where('placement', $placement)->values()),
            ]);

        return $this->success('', $grouped);
    }

    public function click($id)
    {
        Banner::whereKey($id)->increment('clicks');

        return $this->success('');
    }
}
