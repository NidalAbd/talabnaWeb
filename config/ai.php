<?php

/**
 * Paid AI helpers (post creation). Everything has a default so nothing needs to be added to .env;
 * set an AI_* variable only to override one.
 */
return [
    // Actions costing at least this many points ask the user to confirm; cheaper ones are one tap.
    'confirm_from' => (int) env('AI_CONFIRM_FROM', 3),

    'text_model' => env('AI_TEXT_MODEL', 'gpt-4o-mini'),
    'image_model' => env('AI_IMAGE_MODEL', 'gpt-image-1'),
    'image_quality' => env('AI_IMAGE_QUALITY', 'medium'),
    'image_size' => env('AI_IMAGE_SIZE', '1024x1024'),
    'video_model' => env('AI_VIDEO_MODEL', 'sora-2'),
    'video_seconds' => env('AI_VIDEO_SECONDS', '4'),
    'video_size' => env('AI_VIDEO_SIZE', '720x1280'),

    // A request still "processing" after this long is given up on and refunded.
    'stale_minutes' => ['text' => 3, 'image' => 6, 'video' => 20],

    // Generated files are kept this long so a lost response can still be fetched.
    'result_days' => 7,
];
