<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::active()->get();

        if ($banners->isNotEmpty()) {
            Banner::whereIn('id', $banners->pluck('id'))->increment('view_count');
        }

        return response()->json([
            'banners' => $banners->map(fn (Banner $banner) => [
                'id' => $banner->id,
                'title' => $banner->title,
                'subtitle' => $banner->subtitle,
                'image_url' => $banner->image_url,
                'link_url' => $banner->link_url,
                'company_name' => $banner->company_name,
                'sponsor_label' => __('Sponsorisé'),
            ])->values(),
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function trackClick(Banner $banner): JsonResponse
    {
        $banner->increment('click_count');

        return response()->json(['success' => true]);
    }
}
