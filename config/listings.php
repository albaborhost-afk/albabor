<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Listing image watermark
    |--------------------------------------------------------------------------
    |
    | Path to the Albabor logo image used as watermark on all listing photos.
    | Use a PNG with transparency; recommended size ~400px wide.
    | Set to null or ensure file does not exist to disable watermarking.
    |
    */
    'watermark_path' => env('LISTING_WATERMARK_PATH', base_path('public/images/watermark.png')),

    /** Opacity of the watermark (0 = transparent, 100 = opaque). */
    'watermark_opacity' => (int) env('LISTING_WATERMARK_OPACITY', 30),

    /** Position: bottom-right, bottom, center, top-right, etc. */
    'watermark_position' => env('LISTING_WATERMARK_POSITION', 'bottom'),

    /** Offset in pixels from the position. */
    'watermark_offset_x' => (int) env('LISTING_WATERMARK_OFFSET_X', 0),
    'watermark_offset_y' => (int) env('LISTING_WATERMARK_OFFSET_Y', 30),

    /** Max width of the watermark as a fraction of the image width (e.g. 0.30 = 30%). */
    'watermark_max_width_ratio' => 0.30,

];
