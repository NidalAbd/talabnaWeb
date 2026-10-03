<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Texts the Talabna app uses that were missing on the server (2026-10-04 sweep: 890 of the app's
 * keys had no row, so every language but the code's English/Arabic showed English).
 *
 * Adds the English and Arabic rows only where a key has no value yet, so translations edited in
 * the admin are never overwritten. Arabic is the source locale for `php artisan translate:all`,
 * which then fills the other languages; run `translations:export` afterwards to refresh the
 * app's offline bundle.
 *
 * Keys of the form "<group>.<key>"; for texts the app builds with trAr()/AiText.t() the key part is
 * the slug of the English text (trSlug() in the app: lowercase, non-alphanumerics -> "_", max 48).
 */
class TalabnaAppStringsSeeder extends Seeder
{
    public function run(): void
    {
        $strings = json_decode(file_get_contents(__DIR__.'/data/talabna_app_strings.json'), true);
        $now = now();
        $added = 0;
        $touched = [];

        foreach ($strings as $fullKey => $values) {
            [$group, $key] = explode('.', $fullKey, 2);
            foreach (['en', 'ar'] as $locale) {
                $value = $values[$locale] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                $row = DB::table('translations')->where(compact('locale', 'group', 'key'))->first();
                if ($row && trim((string) $row->value) !== '') {
                    continue;
                }
                if ($row) {
                    DB::table('translations')->where('id', $row->id)->update(['value' => $value, 'updated_at' => $now]);
                } else {
                    DB::table('translations')->insert(compact('locale', 'group', 'key', 'value') + ['created_at' => $now, 'updated_at' => $now]);
                }
                $added++;
                $touched["{$locale}|{$group}"] = true;
            }
        }

        // Rows were written with the query builder, so the model's cache-busting hook didn't run.
        foreach (array_keys($touched) as $pair) {
            [$locale, $group] = explode('|', $pair);
            Translation::clearCache($locale, $group);
        }

        $this->command?->info("Talabna app strings: {$added} rows added");
    }
}
