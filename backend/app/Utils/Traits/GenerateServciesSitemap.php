<?php

namespace App\Utils\Traits;

use App\Models\Service;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Sitemap as TagsSitemap;
use Spatie\Sitemap\Tags\Url;

trait GenerateServciesSitemap
{
    private Sitemap $servicesSitemap;

    private function addServiceUrlsToSitemap()
    {
        $services = Service::get(['id', 'slug']);

        foreach ($services as $service) {
            $route = route('services.show', ['service' => $service->slug]);

            $url = Url::create($route);

            $url->addAlternate($route, 'x-default');

            foreach ($this->locales as $locale) {
                $alternateRoute = route('services.show', ['locale' => $locale, 'service' => $service->slug]);

                $url->addAlternate($alternateRoute, $locale);
            }

            $this->servicesSitemap->add($url);
        }
    }

    protected function generateServicesSitemap()
    {
        $this->servicesSitemap = Sitemap::create();

        $this->addServiceUrlsToSitemap();

        $this->servicesSitemap->writeToFile(public_path("sitemaps/services.xml"));

        $sitemapUrl = TagsSitemap::create("{$this->sitemapsUrl}/services.xml");

        $this->sitemapsIndex->add($sitemapUrl);
    }
}
