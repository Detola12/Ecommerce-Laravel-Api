<?php

namespace App\Repositories;

use App\Http\Requests\CreateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ProductRepository
{
    public function getProductById($id)
    {
        return Product::find($id) ?? null;
    }

    public function getProducts($size = 10)
    {
        return Product::query()->with('category');
    }

    public function updateProduct(Request $request, Product $product)
    {
        return DB::transaction(function () use($request, $product){
            $product->name = $request->name ?? $product->name;
            $product->price = $request->price ?? $product->price;
            $product->slug = $request->name !== null ? Str::slug($request->name) . '-' . rand(1, 20)  : $product->slug;
            return $product->save();
        });
    }

    public function createProduct(Request | CreateProductRequest $request)
    {
        return DB::transaction(function () use ($request){
            return Product::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'price' => $request->price,
                'slug' => Str::slug($request->name) . '-' . rand(1, 20) ,
                'category_id' => $request->category_id
            ]);
        });
    }
}
