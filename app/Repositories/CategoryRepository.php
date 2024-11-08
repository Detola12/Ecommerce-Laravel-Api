<?php

namespace App\Repositories;

use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public function categoryById($categoryId)
    {
        return Category::find($categoryId) ?? null;
    }

    public function getCategories($size = 10)
    {
        return CategoryResource::collection(Category::paginate($size));
    }
}
