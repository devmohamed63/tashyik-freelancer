<?php

namespace App\Console\Commands;

use App\Utils\Traits\GenerateCategoriesSitemap;
use App\Utils\Traits\GenerateServciesSitemap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Spatie\Sitemap\SitemapIndex;

class GenerateSitemaps extends Command
{
    use GenerateCategoriesSitemap,
        GenerateServciesSitemap;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemaps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Sitemaps';

    private string $defaultLocale;

    private array $locales;

    private SitemapIndex $sitemapsIndex;

    private string $sitemapsUrl;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!is_dir(public_path('sitemaps'))) {
            mkdir(public_path('sitemaps'));
        }

        $this->sitemapsUrl = env('FRONTEND_URL') . '/sitemaps';

        $this->defaultLocale = config('app.fallback_locale');

        $this->locales = array_filter(config('app.translation_languages'), fn($locale) => $locale != $this->defaultLocale);

        URL::defaults([
            'locale' => $this->defaultLocale
        ]);

        $this->sitemapsIndex = SitemapIndex::create();

        $this->generateCategoriesSitemap();

        $this->generateServicesSitemap();

        $this->sitemapsIndex->writeToFile(public_path("sitemaps/index.xml"));
    }
}
