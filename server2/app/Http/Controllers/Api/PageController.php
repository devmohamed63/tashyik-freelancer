<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Utils\Http\Controllers\ApiController;

class PageController extends ApiController
{
    public function index()
    {
        $pages = Page::active()
            ->notDefaultPages()
            ->paginate($this->paginationLimit, [
                'id',
                'name',
            ]);

        return PageResource::collection($pages);
    }

    public function show(Page $page)
    {
        abort_if(!$page->isActive(), 404);

        return new PageResource($page);
    }
}
