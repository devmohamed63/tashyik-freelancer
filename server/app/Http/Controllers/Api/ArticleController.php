<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Utils\Http\Controllers\ApiController;

class ArticleController extends ApiController
{
    public function index()
    {
        $limit = request()->input('limit', $this->paginationLimit);
        $limit = is_numeric($limit) ? min((int) $limit, 100) : $this->paginationLimit;

        $articles = Article::published()
            ->latest('published_at')
            ->paginate($limit, [
                'id',
                'title',
                'slug',
                'excerpt',
                'is_featured',
                'published_at',
            ]);

        return ArticleResource::collection($articles);
    }

    public function show(Article $article)
    {
        abort_if(!$article->isActive(), 404);

        // Check if article is scheduled for future
        if ($article->published_at && $article->published_at->isFuture()) {
            abort(404);
        }
//comment
        return new ArticleResource($article);
    }
}
