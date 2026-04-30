<?php

namespace App\Utils\Traits;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Sitemap as TagsSitemap;
use Spatie\Sitemap\Tags\Url;

trait GenerateStaticPagesSitemap
{
    private Sitemap $staticPagesSitemap;

    /**
     * Static pages that exist on the frontend.
     * These are paths relative to the locale prefix (e.g., /ar/about).
     */
    private function getStaticPages(): array
    {
        return [
            ''            => 'daily',   // Homepage (/ar)
            'about'       => 'monthly',
            'categories'  => 'weekly',
            'articles'    => 'weekly',
            'contact'     => 'monthly',
        ];
    }

    private function addStaticPageUrlsToSitemap()
    {
        $frontendUrl = rtrim(env('FRONTEND_URL'), '/');

        // With prefix_except_default strategy:
        // - Default locale (ar): https://www.tashyik.com, https://www.tashyik.com/about
        // - Other locales: https://www.tashyik.com/en, https://www.tashyik.com/en/about

        foreach ($this->getStaticPages() as $path => $changeFreq) {
            // Default locale URL (no prefix)
            $defaultUrl = $path === ''
                ? $frontendUrl
                : "{$frontendUrl}/{$path}";

            $url = Url::create($defaultUrl)
                ->setChangeFrequency($changeFreq)
                ->setPriority($path === '' ? 1.0 : 0.8);

            // x-default points to root (no prefix)
            $url->addAlternate($defaultUrl, 'x-default');
            $url->addAlternate($defaultUrl, $this->defaultLocale);

            // Other locales with prefix
            foreach ($this->locales as $locale) {
                $alternatePath = $path === ''
                    ? "{$frontendUrl}/{$locale}"
                    : "{$frontendUrl}/{$locale}/{$path}";

                $url->addAlternate($alternatePath, $locale);
            }

            $this->staticPagesSitemap->add($url);
        }
    }

    protected function generateStaticPagesSitemap()
    {
        $this->staticPagesSitemap = Sitemap::create();

        $this->addStaticPageUrlsToSitemap();

        $this->staticPagesSitemap->writeToFile(public_path("sitemaps/pages.xml"));

        $sitemapUrl = TagsSitemap::create("{$this->sitemapsUrl}/pages.xml");

        $this->sitemapsIndex->add($sitemapUrl);
    }
}
