<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;

class CategoryController extends Controller
{
    use HttpResponse;
    public function getAllCategories(){
        $categories = Category::paginate(2);
        return $this->dataSuccess($categories);
    }

    public function createCategory(CreateCategoryRequest $request){
        try {
            if($request->has('parent_id')) {
                $category = Category::where('id', $request->parent_id)->first();
                if (!$category) {
                    return $this->error("Parent Category does not exist", 404);
                }

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

                if (!$created) {
                    return $this->error('Category could not be created', 400);
                }
                return $this->success('Category Created Successfully');
            }

            $created = DB::transaction(function () use ($request) {
                return Category::create([
                    'name' => $request->name,
                    'parent_id' =>  null
                ]);
            });
            if (!$created) {
                return $this->error('Category could not be created', 400);
            }
            return $this->success('Category Created Successfully');


        }
        catch (\Exception $e){
            return $this->error($e->getMessage(), 400);
        }
    }

    public function updateCategory(Request $request, $id){
        try {
            $request->validate([
                'name' => 'string',
                'parent_id' => 'integer'
            ]);
            $category = Category::find($id);
            if(!$category){
                return $this->error("Category does not exists", 404);
            }
            $parentId = $request->input('parent_id');
            if($parentId){
                $parentCategory = Category::find($parentId);
                if (!$parentCategory){
                    return $this->error("Parent category does not exists", 404);
                }
                $updated = DB::transaction( function () use($request, $category){
                    $category->name = $request->name ?? $category->name;
                    $category->parent_id = $request->parent_id ?? $category->parent_id;
                    $category->save();
                });
            }
            else{
                $updated = DB::transaction( function () use($request, $category){
                    $category->name = $request->name ?? $category->name;
                    $category->save();
                });
            }
            return $this->success("Category Updated", $updated);

        }
        catch (\Exception $e){
            return $this->error($e->getMessage(), 400);
        }
    }

    public function deleteCategory($id){
        $category = Category::find($id);
        if(!$category){
            return $this->error("Category does not exist", 404);
        }
        $category->forceDelete();
        return $this->success("Category deleted successfully");
    }
}
