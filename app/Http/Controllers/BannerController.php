<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;

class BannerController extends Controller
{
    public function trackClick(Banner $banner): RedirectResponse
    {
        $banner->increment('click_count');

        if ($banner->link_url) {
            return redirect()->away($banner->link_url);
        }

        return redirect()->back();
    }
}
