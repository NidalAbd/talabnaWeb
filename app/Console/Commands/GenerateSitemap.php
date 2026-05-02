<?php

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use App\Models\Categories;
use App\Models\ServicePost;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Pre-render every sitemap chunk to static gzipped files in
 * storage/app/sitemaps/. SitemapController serves these directly when
 * present — no DB queries, no XML build per Google fetch. Run after
 * every deploy so Google sees fresh static files.
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Pre-generate all sitemap chunks as static gzipped files';

    public function handle(): int
    {
        $disk = Storage::disk('local');
        $dir = 'sitemaps';
        $disk->makeDirectory($dir);

        // Wipe any previous files so removed chunks (e.g. when listing count
        // shrinks) don't linger.
        foreach ($disk->files($dir) as $f) {
            $disk->delete($f);
        }

        $controller = app(SitemapController::class);

        $write = function (string $name, string $xml) use ($disk, $dir) {
            $disk->put("{$dir}/{$name}", $xml);
            $disk->put("{$dir}/{$name}.gz", gzencode($xml, 6));
            $this->line(sprintf('  wrote %-45s xml=%-9s gz=%-7s',
                $name, $disk->size("{$dir}/{$name}"), $disk->size("{$dir}/{$name}.gz")));
        };

        $this->info('Generating sitemap index...');
        $write('sitemap.xml', $controller->index()->getContent());

        $this->info('Generating pages + categories...');
        $write('sitemap-pages.xml', $controller->pages()->getContent());
        $write('sitemap-categories.xml', $controller->categories()->getContent());

        $this->info('Generating listings (paginated)...');
        $listingsTotal = ServicePost::where('state', 'published')->count();
        $listingsChunks = max(1, (int) ceil($listingsTotal / SitemapController::listingsPerPage()));
        for ($i = 1; $i <= $listingsChunks; $i++) {
            $write("sitemap-listings-{$i}.xml", $controller->listings($i)->getContent());
        }

        $this->info('Generating locations (paginated)...');
        $locationsTotal = SitemapController::locationRecordsCount();
        $locationsChunks = max(1, (int) ceil($locationsTotal / SitemapController::locationsPerPage()));
        for ($i = 1; $i <= $locationsChunks; $i++) {
            $write("sitemap-locations-{$i}.xml", $controller->locations($i)->getContent());
        }

        $this->info('Generating location-categories (paginated)...');
        $locCatTotal = SitemapController::locationCategoryRecordsCount();
        $locCatChunks = max(1, (int) ceil($locCatTotal / SitemapController::locCatPerPage()));
        for ($i = 1; $i <= $locCatChunks; $i++) {
            $write("sitemap-location-categories-{$i}.xml", $controller->locationCategories($i)->getContent());
        }

        $this->info('Generating users (paginated)...');
        $usersTotal = User::where('is_active', '!=', 'banned')
            ->whereHas('servicePosts', fn($q) => $q->where('state', 'published'))
            ->count();
        $usersChunks = max(1, (int) ceil($usersTotal / SitemapController::usersPerPage()));
        for ($i = 1; $i <= $usersChunks; $i++) {
            $write("sitemap-users-{$i}.xml", $controller->users($i)->getContent());
        }

        $totalFiles = count($disk->files($dir));
        $this->info("\n✓ Generated {$totalFiles} files (xml + gz pairs)");
        return self::SUCCESS;
    }
}
