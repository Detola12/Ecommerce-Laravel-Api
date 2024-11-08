<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use mysql_xdevapi\Exception;

class CategoryController extends Controller
{
    public function __construct(private CategoryRepository $categoryRepository)
    {
    }

    use HttpResponse;
    public function getAllCategories(){
        $categories = $this->categoryRepository->getCategories();
        return $this->dataSuccess($categories, 'Categories fetched');
    }

    public function createCategory(CreateCategoryRequest $request){
        try {
            if($request->user()->cannot('create', Category::class)){
                return $this->error('Not authorized', 400);
            }
            $category = $this->categoryRepository->categoryById($request->parent_id);
            $created = DB::transaction(function () use ($request, $category) {
                if ($category) {
                    $category->sub_category += 1;
                    $category->save();
                }
                return Category::create([
                    'name' => $request->name,
                    'parent_id' => $category->id ?? null
                ]);
            });

            return $created
                ? $this->success('Category Created Successfully')
                : $this->error('Category could not be created', 400);

        }
        catch (\Exception $e){
            return $this->error($e->getMessage(), 400);
        }
    }

    public function updateCategory(Request $request, $id){
        if($request->user()->cannot('update', Category::class)){
            return $this->error('Not authorized', 400);
        }
        try {
            $request->validate([
                'name' => 'string',
                'parent_id' => 'string'
            ]);
            $category = $this->categoryRepository->categoryById($id);
            if(!$category){
                return $this->error("Category does not exists", 404);
            }
            $parentCategory = $request->has('parent_id') ? $this->categoryRepository->categoryById($request->parent_id) : null;
            if ($request->has('parent_id') && $parentCategory->isEmpty()){
                return $this->error("Parent category does not exists", 404);
            }
            $updated = DB::transaction( function () use($request, $category){
                $category->name = $request->name ?? $category->name;
                $category->parent_id = $request->parent_id ?? $category->parent_id;
                $category->save();
            });

            return $this->success('Category Updated');
        }
        catch (\Exception $e){
            return $this->error($e->getMessage(), 400);
        }
    }

    public function deleteCategory(Request $request,$id){
        if($request->user()->cannot('update', Category::class)){
            return $this->error('Not authorized', 400);
        }
        $category = Category::find($id);
        if(!$category){
            return $this->error("Category does not exist", 404);
        }
        $category->forceDelete();
        return $this->success("Category deleted successfully");
    }
}
