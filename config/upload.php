<?php

return [
    'image_types' => [
        'avatar' => [
            'crop' => true,
            'full_size' => [360, 360],
            'thumb_size' => [100, 100],
        ],
    ],

    'path_origin_image' => 'originals',

    'path_thumbnail' => 'thumbnails',

    'disk' => env('IMAGE_DISK', 'upload'),

    'webp_ext' => 'webp',

    'webp_quality' => env('IMAGE_WEBP_QUALITY', 90),

    'size_max' => env('IMAGE_SIZE_MAX', 5120)

];
