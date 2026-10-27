<?php

namespace App\Http\Requests\Dashboard;

class UpdateBannerRequest extends StoreBannerRequest
{
    public function authorize()
    {
        return abilities()->contains('update_banners');
    }

    protected function imageRequired(): bool
    {
        // Keeping the current picture is fine; only ask for one if there is none yet
        return parent::imageRequired() && !$this->route('banner')?->image;
    }
}
