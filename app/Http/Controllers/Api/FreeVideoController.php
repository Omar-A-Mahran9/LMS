<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FreeVideoResource;
use App\Models\FreeVideo;
use Illuminate\Http\Request;

class FreeVideoController extends Controller
{
    /**
     * Free videos / tips, open to everyone.
     * Filters: category_id (that grade + the all-grades videos), search, featured=1,
     * sort=newest|popular|oldest (default: dashboard order), per_page.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $videos = FreeVideo::published()
            ->with('category')
            ->when($request->integer('category_id'), fn($q, $id) => $q->forCategory($id))
            ->when($request->boolean('featured'), fn($q) => $q->where('is_featured', true))
            ->when($search !== '', fn($q) => $q->where(fn($s) => $s
                ->where('title_ar', 'like', "%{$search}%")
                ->orWhere('title_en', 'like', "%{$search}%")))
            ->sorted($request->get('sort'))
            ->paginate(min(max($request->integer('per_page', 12), 1), 50));

        return $this->successWithPagination('', FreeVideoResource::collection($videos)->response()->getData(true));
    }

    /** One video with a few related ones (same grade). Each open counts as a view. */
    public function show($id)
    {
        $video = FreeVideo::published()->with('category')->findOrFail($id);
        $video->increment('views');

        $related = FreeVideo::published()
            ->with('category')
            ->whereKeyNot($video->id)
            ->when($video->category_id, fn($q) => $q->forCategory($video->category_id))
            ->sorted()
            ->take(6)
            ->get();

        return $this->success('', [
            'video' => new FreeVideoResource($video),
            'related' => FreeVideoResource::collection($related),
        ]);
    }
}
