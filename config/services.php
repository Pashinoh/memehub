<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'account' => [
        'blocked_names' => array_values(array_filter(array_map('trim', explode(',', (string) env('BLOCKED_ACCOUNT_NAMES', ''))))),
        'admin_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_EMAILS', ''))))),
    ],

    'ffmpeg' => [
        'binary' => env('FFMPEG_BIN', env('FFMPEG_BINARY', '')),
    ],

    'media' => [
        'upload_max_kb' => (int) env('MEDIA_UPLOAD_MAX_KB', 30720),
        'image_webp_quality' => (int) env('MEDIA_IMAGE_WEBP_QUALITY', 74),

        // Low-resource defaults (good for ~2GB RAM server).
        'video_compress_min_kb' => (int) env('MEDIA_VIDEO_COMPRESS_MIN_KB', 6144),
        'video_scale_max_width' => (int) env('MEDIA_VIDEO_SCALE_MAX_WIDTH', 960),
        'video_preset' => (string) env('MEDIA_VIDEO_PRESET', 'superfast'),
        'video_crf' => (int) env('MEDIA_VIDEO_CRF', 30),
        'video_maxrate' => (string) env('MEDIA_VIDEO_MAXRATE', '900k'),
        'video_bufsize' => (string) env('MEDIA_VIDEO_BUFSIZE', '1800k'),
        'video_threads' => (int) env('MEDIA_VIDEO_THREADS', 1),
        'video_audio_bitrate' => (string) env('MEDIA_VIDEO_AUDIO_BITRATE', '80k'),
        'video_timeout_seconds' => (int) env('MEDIA_VIDEO_TIMEOUT_SECONDS', 90),

        'gif_compress_min_kb' => (int) env('MEDIA_GIF_COMPRESS_MIN_KB', 4096),
        'gif_fps' => (int) env('MEDIA_GIF_FPS', 10),
        'gif_scale_max_width' => (int) env('MEDIA_GIF_SCALE_MAX_WIDTH', 720),
        'gif_threads' => (int) env('MEDIA_GIF_THREADS', 1),
        'gif_timeout_seconds' => (int) env('MEDIA_GIF_TIMEOUT_SECONDS', 60),
    ],

];
