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
        $categoryIds = Category::isParent()->pluck('id');

        foreach ($categoryIds as $id) {
            $route = route('categories.show', ['cateogry' => $id]);

            $url = Url::create($route);

            $url->addAlternate($route, 'x-default');

            foreach ($this->locales as $locale) {
                $alternateRoute = route('categories.show', ['locale' => $locale, 'cateogry' => $id]);

                $url->addAlternate($alternateRoute, $locale);
            }

            $this->categoriesSitemap->add($url);
        }
    }

    private function addSubcategoryUrlsToSitemap()
    {
        $subcategoryIds = Category::isChild()->pluck('id');

        foreach ($subcategoryIds as $id) {
            $route = route('subcategories.show', ['cateogry' => $id]);

            $url = Url::create($route);

            $url->addAlternate($route, 'x-default');

            foreach ($this->locales as $locale) {
                $alternateRoute = route('subcategories.show', ['locale' => $locale, 'cateogry' => $id]);

                $url->addAlternate($alternateRoute, $locale);
            }

            $this->categoriesSitemap->add($url);
        }
    }

    protected function generateCategoriesSitemap()
    {
        $this->categoriesSitemap = Sitemap::create();

        $this->addCategoryUrlsToSitemap();

        $this->addSubcategoryUrlsToSitemap();

        $this->categoriesSitemap->writeToFile(public_path("sitemaps/categories.xml"));

        $sitemapUrl = TagsSitemap::create("{$this->sitemapsUrl}/categories.xml");

        $this->sitemapsIndex->add($sitemapUrl);
    }
}
