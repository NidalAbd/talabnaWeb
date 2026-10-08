<?php

/**
 * Paid AI helpers (post creation). Everything has a default so nothing needs to be added to .env;
 * set an AI_* variable only to override one.
 */
return [
    // Actions costing at least this many points ask the user to confirm; cheaper ones are one tap.
    'confirm_from' => (int) env('AI_CONFIRM_FROM', 3),

    'text_model' => env('AI_TEXT_MODEL', 'gpt-4o-mini'),

    // Text/vision (AiTextChain, 2026-10-08): providers in the order they are tried; a failing one is skipped for a few
    // minutes and the next answers at once. Each request names a tier so small jobs use small models.
    'text_providers' => explode(',', env('AI_TEXT_PROVIDERS', 'openai,claude,gemini')),
    'tiers' => [
        // Language check, "is there an item in this photo".
        // gpt-4.1-nano is shut down on 2026-10-23 (OpenAI deprecations); gpt-4o-mini has no shutdown date.
        'light' => ['openai' => 'gpt-4o-mini', 'claude' => 'claude-haiku-5-5', 'gemini' => 'gemini-3.1-flash-lite'],
        // Post writing, translation, category, price, photo-to-ad, video brief.
        'standard' => ['openai' => env('AI_TEXT_MODEL', 'gpt-4o-mini'), 'claude' => 'claude-haiku-5-5', 'gemini' => 'gemini-3.1-flash-lite'],
        // Kept for larger jobs; nothing uses it yet.
        'heavy' => ['openai' => 'gpt-4.1', 'claude' => 'claude-sonnet-5-5', 'gemini' => 'gemini-2.5-flash'],
    ],
    'image_model' => env('AI_IMAGE_MODEL', 'gpt-image-1'),
    'image_quality' => env('AI_IMAGE_QUALITY', 'medium'),
    // Vertical, for a phone screen (square filled a quarter of it, 2026-10-08).
    'image_size' => env('AI_IMAGE_SIZE', '1024x1536'),
    // 2026-10-07: pro model, vertical HD, 8 s (sora-2 at 720p/4 s looked dated). About $4 per clip.
    'video_model' => env('AI_VIDEO_MODEL', 'sora-2-pro'),
    'video_seconds' => env('AI_VIDEO_SECONDS', '8'),
    'video_size' => env('AI_VIDEO_SIZE', '1024x1792'),
    // Veo (Gemini API), used once Sora is gone or fails: vertical 9:16, 1080p for 8 s clips, 720p for shorter ones.
    'veo_model' => env('AI_VEO_MODEL', 'veo-3.1-generate-preview'),
    'veo_resolution' => env('AI_VEO_RESOLUTION', '1080p'),
    // Backups (2026-10-08): Veo Fast when Veo is not ready; Gemini's image model when gpt-image-1 is not.
    'veo_fast_model' => env('AI_VEO_FAST_MODEL', 'veo-3.1-fast-generate-preview'),
    'gemini_image_model' => env('AI_GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),

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
