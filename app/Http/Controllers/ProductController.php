<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Models\Product;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use HttpResponse;
    public function getAllProducts(Request $request){
        $pageSize = $request->query('pageSize');
        $price = $request->query('price');
        $category = $request->query('category');
        $products = Product::query()->with('category');
        if($category){
            $products->whereHas('category', function ($query) use ($category) {
                $query->where('name', 'LIKE', $category . '%');
            });
        }
        if($price){
            $products->orderBy('price', 'desc');
        }
        $products = $products->paginate($pageSize);


        return $this->dataSuccess($products->items(), "Products retrieved");
    }

    public function createProduct(CreateProductRequest $request){
        $product = Product::where('name', $request->name)->first();
        if($request->has('merchant_id')){
            if($product->merchant_id == $request->merchant_id){
                return $this->error("Duplicate Product", 400);
            }
        }

        $created = DB::transaction(function () use ($request){
            return Product::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'price' => $request->price,
                'slug' => Str::slug($request->name) . '-' . now()->toDateString() . '-' . rand(1, 20) ,
                'merchant_id' => 7,
                'category_id' => $request->category_id
            ]);
        });

        if(!$created){
            return $this->error("Product could not be created", 400);
        }
        return $this->success("Product Created", 201);

    }

    public function updateProduct(Request $request, $id){
        $request->validate([
            'name' => 'string',
            'price' => 'decimal:0,2'
        ]);

        $product = Product::find($id);
        if(!$product){
            if($product->merchant_id == $request->merchant_id && $product->name == $request->name){
                return $this->error("Duplicate Products", 400);
            }
            return $this->error("Product not found", 404);
        }
        $updated = false;

        $updated = DB::transaction(function () use($request, $product){
           $product->name = $request->name ?? $product->name;
           $product->price = $request->price ?? $product->price;
           $product->slug = Str::slug($request->name) . now()->toDateTimeString() ?? $product->slug;
           return true;
        });

        if(!$updated){
            return $this->error("Something Went Wrong", 400);
        }
        return $this->success("Product Updated Successfully", 201);
    }

    public function deleteProduct($id){
        $product = Product::find($id);
        if(!$product){
            return $this->error("Product does not exist", 404);
        }
        $product->forceDelete();
        return $this->success("Product deleted successfully");
    }

}
