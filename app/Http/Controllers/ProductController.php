<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // GET /api/v1/products?q=&category=&is_active=&per_page=
    public function index(Request $request)
    {
        $q         = $request->query('q');
        $category  = $request->query('category');
        // Aceptar is_active o active como filtro
        $activeQry = $request->query('is_active', $request->query('active'));
        $perPage   = (int) $request->query('per_page', 10);

        $query = Product::query();

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%")
                  ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($activeQry !== null) {
            $query->where('is_active', filter_var($activeQry, FILTER_VALIDATE_BOOL));
        }

        // Ordenar por created_at DESC (mejor para UUID) y mantener los filtros en los links
       $products = $query->orderByDesc('created_at')->paginate($perPage);


        return ProductResource::collection($products);
    }

    // POST /api/v1/products
    public function store(Request $request)
    {
        $data = $request->validate([
    'code'      => ['required','string','max:50','unique:products,code'],
    'name'      => ['required','string','min:3','max:120'],
    'category'  => ['nullable','string','in:electronics,clothing,food,otros'],
    'price'     => ['required','numeric','gt:0'],
    'stock'     => ['required','integer','min:0'],
    'is_active' => ['sometimes','boolean'],
    'active'    => ['sometimes','boolean'], // alias
    'image_url' => ['nullable','url'],
]);

if ($request->has('active')) {
    $data['is_active'] = $request->boolean('active');
}
unset($data['active']);


        $product = Product::create($data);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    // GET /api/v1/products/{product}
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    // PUT/PATCH /api/v1/products/{product}
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
    'code'      => ['sometimes','required','string','max:50', Rule::unique('products','code')->ignore($product->id)],
    'name'      => ['sometimes','required','string','min:3','max:120'],
    'category'  => ['nullable','string','in:electronics,clothing,food,otros'],
    'price'     => ['sometimes','required','numeric','gt:0'],
    'stock'     => ['sometimes','required','integer','min:0'],
    'is_active' => ['sometimes','boolean'],
    'active'    => ['sometimes','boolean'],
    'image_url' => ['nullable','url'],
]);

if ($request->has('active')) {
    $data['is_active'] = $request->boolean('active');
}
unset($data['active']);

        $product->update($data);

        return new ProductResource($product);
    }

    // DELETE /api/v1/products/{product}
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
