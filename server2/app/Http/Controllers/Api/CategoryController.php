<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function index()
    {
        $categories = Category::isParent()
            ->with('media')
            ->orderBy('item_order')
            ->get(['id', 'slug', 'name', 'badge', 'description']);

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        $category->load([
            'media',
            'children:id,slug,category_id,name',
            'children.media'
        ]);

        return new CategoryResource($category);
    }
}
