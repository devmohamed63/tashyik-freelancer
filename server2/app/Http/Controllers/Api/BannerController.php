<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Utils\Http\Controllers\ApiController;

class BannerController extends ApiController
{
    public function index()
    {
        $banners = Banner::active()
            ->with(['media'])
            ->paginate($this->paginationLimit, ['id', 'name', 'url']);

        return BannerResource::collection($banners);
    }
}
