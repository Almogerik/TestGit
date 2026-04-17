<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $wholesalerId = $request->query('wholesaler_id');
        $query = Promotion::with('product:id,name,image_url,unit')
            ->where('is_active', true)
            ->where('ends_at', '>=', now());

        if ($wholesalerId) {
            $query->where('wholesaler_id', $wholesalerId);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'wholesaler') {
            abort(403, 'Réservé aux grossistes.');
        }

        $validated = $request->validate([
            'product_id'       => 'nullable|exists:products,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'promo_price'      => 'nullable|numeric|min:0',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
        ]);

        $promo = $request->user()->promotions()->create($validated);

        return response()->json($promo, 201);
    }

    public function destroy(Request $request, Promotion $promotion)
    {
        if ($promotion->wholesaler_id !== $request->user()->id) {
            abort(403);
        }
        $promotion->update(['is_active' => false]);
        return response()->json(['message' => 'Promotion désactivée.']);
    }
}
