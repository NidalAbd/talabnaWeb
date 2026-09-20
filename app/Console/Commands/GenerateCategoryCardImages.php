<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Services\CategoryCardImageService;
use Illuminate\Console\Command;

class GenerateCategoryCardImages extends Command
{
    protected $signature = 'categories:generate-card-images
                            {--id=* : Only these category IDs}
                            {--all : Regenerate even where card images already exist}
                            {--quality=high : Image quality (low, medium, high) — sets the cost per image}
                            {--dry-run : List what would be generated and call nothing}';

    protected $description = 'Generate the wide hero (banner_src) and tile (tile_src) images for the app\'s category cards';

    /** The app never shows these (categories_tab.dart filters ids 6 = Near and 7 = Reels). */
    private const HIDDEN_IN_APP = [6, 7];

    public function handle(): int
    {
        $query = Categories::query()
            ->where('isSuspended', false)
            ->whereNotIn('id', self::HIDDEN_IN_APP)
            ->orderBy('id');

        if ($ids = array_filter((array) $this->option('id'))) {
            $query->whereIn('id', $ids);
        }
        if (!$this->option('all')) {
            $query->where(fn ($q) => $q->whereNull('banner_src')->orWhereNull('tile_src'));
        }

        $categories = $query->get();
        if ($categories->isEmpty()) {
            $this->info('Nothing to do: every category already has card images (use --all to regenerate).');

            return self::SUCCESS;
        }

        $quality = (string) $this->option('quality');
        if (!in_array($quality, ['low', 'medium', 'high'], true)) {
            $this->error('--quality must be low, medium or high.');

            return self::INVALID;
        }

        $this->table(['ID', 'Name', 'Has banner', 'Has tile'], $categories->map(fn ($c) => [
            $c->id,
            $c->name['en'] ?? $c->name['ar'] ?? '?',
            $c->banner_src ? 'yes' : 'no',
            $c->tile_src ? 'yes' : 'no',
        ])->all());
        $this->line(sprintf('%d image request(s) at quality "%s" (%s, one paid API call each).',
            $categories->count(), $quality, CategoryCardImageService::SOURCE_SIZE));

        if ($this->option('dry-run')) {
            $this->info('Dry run: nothing generated.');

            return self::SUCCESS;
        }

        $service = new CategoryCardImageService(quality: $quality);
        $failed = 0;
        foreach ($categories as $category) {
            $this->output->write("Category {$category->id} … ");
            if ($service->generateForCategory($category)) {
                $this->info('done');
            } else {
                $failed++;
                $this->error('FAILED: ' . $service->getLastError());
            }
        }

        $this->line(sprintf('%d generated, %d failed.', $categories->count() - $failed, $failed));

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
