<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Traits\HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly CategoryRepository $categoryRepository,
                                private readonly ProductRepository $productRepository)
    {
    }

    use HttpResponse;
    public function getAllProducts(Request $request){
        $pageSize = $request->query('pageSize');
        $category = $request->query('category');
        $products = $this->productRepository->getProducts();

        /************* Query conditions *************/
        $products->when($request->query('minPrice'), function ($products) use ($request){
            $products->where('price', '>=' , $request->query('minPrice'));
        })->when($request->query('maxPrice'), function ($products) use ($request){
            $products->where('price', '<=' , $request->query('maxPrice'));
        })->when($request->query('category'), function ($products) use ($request, $category){
            $products->where('name', 'LIKE', '%' . $category . '%');
        });

        $count = $products->count();

        $products = $products->paginate($pageSize);
        return $this->dataSuccess(['count' => $count,'products' => $products->items()], "Products retrieved");
    }

    public function createProduct(CreateProductRequest $request){
        try {
            if ($request->user()->can('create', Product::class)){
                return $this->error('Not authorized', 400);
            }

            $created = $this->productRepository->createProduct($request);
            if(!$created){
                return $this->error("Product could not be created", 400);
            }
            return $this->dataSuccess($created->makeHidden('id'),"Product Created", 201);
        }
        catch (\Exception $exception){
            Log::error('Something went wrong : ' . $exception);
            return $this->error('Something went wrong', 500);
        }
    }

    public function updateProduct(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'price' => 'decimal:0,2',
            'category_id' => 'int'
        ]);

        if ($validator->fails()){
            return $this->requestError($validator->errors());
        }

        $product = $this->productRepository->getProductById($id);
        if(!$product){
            return $this->error("Product not found", 404);
        }

        $updated = $this->updateProduct($request, $product);

        if(!$updated){
            return $this->error("Something Went Wrong", 400);
        }
        return $this->success("Product Updated Successfully", 201);
    }

    public function deleteProduct($id){
        $product = $this->productRepository->getProductById($id);
        if(!$product){
            return $this->error("Product does not exist", 404);
        }
        $product->forceDelete();
        return $this->success("Product deleted successfully");
    }

}
