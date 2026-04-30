<?php

namespace App\Utils\Traits;

use App\Models\Article;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Sitemap as TagsSitemap;
use Spatie\Sitemap\Tags\Url;

trait GenerateArticlesSitemap
{
    private Sitemap $articlesSitemap;

    private function addArticleUrlsToSitemap()
    {
        $articles = Article::published()->get(['id', 'slug']);

        foreach ($articles as $article) {
            // Default locale (no prefix)
            $route = route('articles.show', ['article' => $article->slug]);

            $url = Url::create($route);

            $url->addAlternate($route, 'x-default');
            $url->addAlternate($route, $this->defaultLocale);

            foreach ($this->locales as $locale) {
                $alternateRoute = route('articles.show.localized', [
                    'locale' => $locale,
                    'article' => $article->slug,
                ]);

                $url->addAlternate($alternateRoute, $locale);
            }

            $this->articlesSitemap->add($url);
        }
    }

    protected function generateArticlesSitemap()
    {
        $this->articlesSitemap = Sitemap::create();

        $this->addArticleUrlsToSitemap();

        $this->articlesSitemap->writeToFile(public_path("sitemaps/articles.xml"));

        $sitemapUrl = TagsSitemap::create("{$this->sitemapsUrl}/articles.xml");

        $this->sitemapsIndex->add($sitemapUrl);
    }
}
