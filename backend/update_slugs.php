<?php

use App\Models\Category;
use Illuminate\Support\Str;

$categories = Category::all();
foreach ($categories as $category) {
    if (empty($category->slug)) {
        $category->slug = Category::generateUniqueSlug($category->getTranslation('name', 'ar', false) ?: $category->getTranslation('name', 'en', false) ?: 'cat-' . $category->id);
        $category->save();
        echo "Updated category {$category->id} with slug: {$category->slug}\n";
    }
}
