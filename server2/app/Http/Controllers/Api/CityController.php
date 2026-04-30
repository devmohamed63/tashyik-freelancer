<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CityResource;
use App\Models\City;
use App\Utils\Http\Controllers\ApiController;

class CityController extends ApiController
{
    public function index()
    {
        $cities = City::orderBy('item_order')
            ->paginate($this->paginationLimit, [
                'id',
                'name'
            ]);

        return CityResource::collection($cities);
    }
}
