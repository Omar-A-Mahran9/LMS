<?php

namespace App\Http\Resources\Api;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'placement'       => $this->placement,
            'title'           => $this->localized('title'),
            'subtitle'        => $this->localized('subtitle'),
            'badge'           => $this->localized('badge'),
            'image'           => $this->full_image_path,
            'mobile_image'    => $this->full_mobile_image_path ?? $this->full_image_path,
            'btn_name'        => $this->localized('btn_title'),
            'link'            => $this->link,
            'open_in_new_tab' => $this->open_in_new_tab,
            'bg_color'        => $this->bg_color,
            'text_color'      => $this->text_color,
            'ends_at'         => Banner::toIso($this->ends_at),
            'show_countdown'  => $this->show_countdown && $this->ends_at,
        ];
    }
}
