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
    // 2026-10-07: pro model, vertical HD, 8 s (sora-2 at 720p/4 s looked dated). About $4 per clip.
    'video_model' => env('AI_VIDEO_MODEL', 'sora-2-pro'),
    'video_seconds' => env('AI_VIDEO_SECONDS', '8'),
    'video_size' => env('AI_VIDEO_SIZE', '1024x1792'),
    // Veo (Gemini API), used once Sora is gone or fails: vertical 9:16, 1080p for 8 s clips, 720p for shorter ones.
    'veo_model' => env('AI_VEO_MODEL', 'veo-3.1-generate-preview'),
    'veo_resolution' => env('AI_VEO_RESOLUTION', '1080p'),

    // A request still "processing" after this long is given up on and refunded.
    'stale_minutes' => ['text' => 3, 'image' => 6, 'video' => 30], // sora-2-pro HD clips can take 10+ min

    // Extra photos/videos on a post: the first `free_media` are free, up to `max_media` in total, and each one beyond the
    // free ones costs `extra_media_points`. Charged when the post is saved, in the same transaction as the post.
    'media' => [
        'free' => (int) env('POST_FREE_MEDIA', 4),
        'max' => (int) env('POST_MAX_MEDIA', 10),
        'extra_points' => (int) env('POST_EXTRA_MEDIA_POINTS', 1),
    ],

    // Load protection for generation: nothing is charged when the system is busy or the daily limit is reached.
    'limits' => [
        'parallel_image' => (int) env('AI_PARALLEL_IMAGE', 4),
        'parallel_video' => (int) env('AI_PARALLEL_VIDEO', 6),
        'daily_media_per_user' => (int) env('AI_DAILY_MEDIA_PER_USER', 30),
    ],

    // Generated files are kept this long so a lost response can still be fetched.
    'result_days' => 7,
];
