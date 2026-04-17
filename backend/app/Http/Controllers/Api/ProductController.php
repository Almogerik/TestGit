<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $wholesalerId = $request->query('wholesaler_id');
        $category     = $request->query('category');
        $search       = $request->query('search');

        $query = Product::with('wholesaler:id,name,store_name')
            ->where('is_active', true);

        if ($wholesalerId) {
            $query->where('wholesaler_id', $wholesalerId);
        }
        if ($category) {
            $query->where('category', $category);
        }
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $this->authorizeWholesaler($request);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'price'       => 'required|numeric|min:0',
            'unit'        => 'required|string|max:50',
            'stock_qty'   => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path     = $request->file('image')->store('products', 'public');
            $imageUrl = Storage::url($path);
        }

        $product = $request->user()->products()->create([
            'name'        => $validated['name'],
            'category'    => $validated['category'],
            'price'       => $validated['price'],
            'unit'        => $validated['unit'],
            'stock_qty'   => $validated['stock_qty'],
            'description' => $validated['description'] ?? null,
            'image_url'   => $imageUrl,
        ]);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('wholesaler:id,name,store_name,phone'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'category'    => 'sometimes|string|max:100',
            'price'       => 'sometimes|numeric|min:0',
            'unit'        => 'sometimes|string|max:50',
            'stock_qty'   => 'sometimes|integer|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path                  = $request->file('image')->store('products', 'public');
            $validated['image_url'] = Storage::url($path);
        }

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeOwner($request, $product);
        $product->update(['is_active' => false]);
        return response()->json(['message' => 'Produit désactivé.']);
    }

    private function authorizeWholesaler(Request $request)
    {
        if ($request->user()->role !== 'wholesaler') {
            abort(403, 'Réservé aux grossistes.');
        }
    }

    private function authorizeOwner(Request $request, Product $product)
    {
        $this->authorizeWholesaler($request);
        if ($product->wholesaler_id !== $request->user()->id) {
            abort(403, 'Non autorisé.');
        }
    }
}
