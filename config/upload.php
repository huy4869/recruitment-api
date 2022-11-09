<?php

return [
    'image_types' => [
        'avatar_banner' => [
            'crop' => true,
            'full_size' => [319, 319],
            'thumb_size' => [190, 190],
        ],
        'avatar_detail' => [
            'crop' => true,
            'full_size' => [140, 140],
            'thumb_size' => [100, 100],
        ],
        'job_banner' => [
            'crop' => true,
            'full_size' => [866, 577],
            'thumb_size' => [195, 130],
        ],
        'job_detail' => [
            'crop' => true,
            'full_size' => [276, 184],
            'thumb_size' => [195, 130],
        ],
        'store_banner' => [
            'crop' => true,
            'full_size' => [130, 130],
            'thumb_size' => [100, 100],
        ],
    ],

    'path_origin_image' => 'originals',

    'path_thumbnail' => 'thumbnails',

    'disk' => env('IMAGE_DISK', 'upload'),

    'webp_ext' => 'webp',

    'webp_quality' => env('IMAGE_WEBP_QUALITY', 90),

    'size_max' => env('IMAGE_SIZE_MAX', 20480),

];
