<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Some translations were machine-filled with Dart-style "$name" placeholders, which the app
 * (before 1.5.3) shows literally, e.g. "Following $categoryName". The app fills {name}.
 * Rewrites "$name" -> "{name}" for the known keys in every locale. Original rows are saved to
 * storage/app/translations-dollar-backup-<time>.json first.
 */
return new class extends Migration
{
    private array $keys = [
        'follow.following', 'follow.unfollowed', 'media.max_images_limit',
        'points.points_deduction', 'post.view_post_share',
    ];

    public function up(): void
    {
        $rows = DB::table('translations')
            ->where('value', 'like', '%$%')
            ->whereIn(DB::raw("concat(`group`,'.',`key`)"), $this->keys)
            ->get();
        Storage::put('translations-dollar-backup-'.date('Ymd-His').'.json', $rows->toJson(JSON_UNESCAPED_UNICODE));

        foreach ($rows as $row) {
            $fixed = preg_replace('/\$(categoryName|max|points|badge|duration|postTitle)\b/u', '{$1}', $row->value);
            if ($fixed !== $row->value) {
                DB::table('translations')->where('id', $row->id)->update(['value' => $fixed, 'updated_at' => now()]);
            }
        }
        foreach (DB::table('translations')->select('locale', 'group')->distinct()->get() as $lg) {
            \App\Models\Translation::clearCache($lg->locale, $lg->group);
        }
    }

    public function down(): void
    {
        // Data fix; the backup file holds the previous values.
    }
};
