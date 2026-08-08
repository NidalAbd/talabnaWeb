<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\ServicePost;
use App\Models\Categories;
use App\Models\countries;
use Illuminate\Console\Command;

class SubmitSitemap extends Command
{
    protected $signature = 'seo:submit {--lang-pages : Submit language variant pages} {--dry-run : Show URLs without submitting}';
    protected $description = 'Generate and display all indexable URLs for submission to Google Search Console';

    public function handle(): int
    {
        $baseUrl = config('app.url', 'https://talbna.cloud');
        $languages = Language::getActiveOrdered();
        $defaultLocale = Language::getDefault()?->code ?? 'ar';
        $dryRun = $this->option('dry-run');
        $langPages = $this->option('lang-pages');

        $urls = [];

        // Static pages
        $pages = ['/', '/browse', '/search', '/about', '/contact', '/privacy', '/terms', '/delete-account'];

        foreach ($pages as $page) {
            $urls[] = $baseUrl . $page;
            if ($langPages) {
                foreach ($languages as $lang) {
                    if ($lang->code !== $defaultLocale) {
                        $urls[] = $baseUrl . $page . '?lang=' . $lang->code;
                    }
                }
            }
        }

        // Category pages
        $categories = Categories::where('isSuspended', false)->get();
        foreach ($categories as $cat) {
            $urls[] = $baseUrl . '/category/' . $cat->id;
        }

        // Country/city service pages
        $countries = countries::with('cities')->get();
        foreach ($countries as $country) {
            $slug = \App\Services\SlugResolver::makeSlug($country->name, 'en');
            if ($slug) {
                $urls[] = $baseUrl . '/services/' . $country->id . '/' . $slug;
            }
        }

        // Latest listings (most important for indexing)
        $posts = ServicePost::where('state', 'published')
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get();

        foreach ($posts as $post) {
            try {
                $url = \App\Services\SlugResolver::buildPostUrl($post, 'ar');
                if ($url) $urls[] = $baseUrl . $url;
            } catch (\Exception $e) {
                $urls[] = $baseUrl . '/listing/' . $post->id;
            }
        }

        $urls = array_unique($urls);

        $this->info("Total URLs: " . count($urls));

        if ($dryRun) {
            foreach ($urls as $url) {
                $this->line($url);
            }
            return 0;
        }

        // Write URLs to a file for bulk submission
        $filePath = storage_path('app/indexable-urls.txt');
        file_put_contents($filePath, implode("\n", $urls));
        $this->info("URLs written to: {$filePath}");
        $this->info("");
        $this->info("To submit to Google, do ONE of these:");
        $this->info("");
        $this->info("  1. Google Search Console → Sitemaps → Submit: https://talbna.cloud/sitemap.xml");
        $this->info("     (Google will auto-discover all URLs from sitemap)");
        $this->info("");
        $this->info("  2. Bulk URL inspection in Search Console:");
        $this->info("     - Go to URL Inspection → paste each important URL → Request Indexing");
        $this->info("     (Limited to ~10-20 per day)");
        $this->info("");
        $this->info("  3. Use the file for third-party indexing tools:");
        $this->info("     cat {$filePath} | head -20");

        return 0;
    }
}
