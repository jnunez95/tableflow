<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_available', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'sort_order']);

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category:id,name,slug')
            ->where('is_available', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'price',
                'image',
                'dietary_tags',
                'sort_order',
            ]);

        return response()->json([
            'data' => $products->map(fn (Product $product) => $this->transformProduct($product)),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_available, 404);

        $product->load('category:id,name,slug');

        return response()->json([
            'data' => $this->transformProduct($product),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformProduct(Product $product): array
    {
        $data = $product->toArray();

        if (! empty($data['image']) && ! str_starts_with($data['image'], 'http')) {
            $data['image'] = tenant_asset($data['image']);
        }

        return $data;
    }
}
