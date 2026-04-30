<?php

namespace App\Utils\Traits;

use App\Models\Category;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Sitemap as TagsSitemap;
use Spatie\Sitemap\Tags\Url;

trait GenerateCategoriesSitemap
{
    private Sitemap $categoriesSitemap;

    private function addCategoryUrlsToSitemap()
    {
        $categories = Category::isParent()->get(['id', 'slug']);

        foreach ($categories as $category) {
            $route = route('categories.show', ['cateogry' => $category->slug]);

            $url = Url::create($route);

            $url->addAlternate($route, 'x-default');

            foreach ($this->locales as $locale) {
                $alternateRoute = route('categories.show', ['locale' => $locale, 'cateogry' => $category->slug]);

                $url->addAlternate($alternateRoute, $locale);
            }

            $this->categoriesSitemap->add($url);
        }
    }

    protected function generateCategoriesSitemap()
    {
        $this->categoriesSitemap = Sitemap::create();

        $this->addCategoryUrlsToSitemap();

        $this->categoriesSitemap->writeToFile(public_path("sitemaps/categories.xml"));

        $sitemapUrl = TagsSitemap::create("{$this->sitemapsUrl}/categories.xml");

        $this->sitemapsIndex->add($sitemapUrl);
    }
}
