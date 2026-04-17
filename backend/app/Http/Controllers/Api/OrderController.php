<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Order::with(['retailer:id,name,store_name,phone', 'wholesaler:id,name,store_name', 'items.product:id,name,unit,image_url']);

        if ($user->role === 'retailer') {
            $query->where('retailer_id', $user->id);
        } elseif ($user->role === 'wholesaler') {
            $query->where('wholesaler_id', $user->id);
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'retailer') {
            abort(403, 'Réservé aux détaillants.');
        }

        $validated = $request->validate([
            'wholesaler_id' => 'required|exists:users,id',
            'note'          => 'nullable|string|max:500',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'retailer_id'   => $request->user()->id,
                'wholesaler_id' => $validated['wholesaler_id'],
                'note'          => $validated['note'] ?? null,
                'status'        => 'pending',
                'total_amount'  => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total   += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal'   => $subtotal,
                ]);
            }

            $order->update(['total_amount' => $total]);
            DB::commit();

            return response()->json($order->load(['items.product', 'retailer:id,name,store_name', 'wholesaler:id,name,store_name']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la commande.'], 500);
        }
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeAccess($request, $order);
        return response()->json($order->load(['items.product', 'retailer:id,name,store_name,phone', 'wholesaler:id,name,store_name,phone']));
    }

    public function update(Request $request, Order $order)
    {
        if ($request->user()->role !== 'wholesaler' || $order->wholesaler_id !== $request->user()->id) {
            abort(403, 'Non autorisé.');
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,delivering,delivered,cancelled',
        ]);

        if ($validated['status'] === 'delivered') {
            $order->update(['status' => $validated['status'], 'delivered_at' => now()]);
        } else {
            $order->update(['status' => $validated['status']]);
        }

        return response()->json($order);
    }

    public function destroy(Request $request, Order $order)
    {
        if ($request->user()->role !== 'retailer' || $order->retailer_id !== $request->user()->id) {
            abort(403, 'Non autorisé.');
        }
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Impossible d\'annuler une commande déjà traitée.'], 422);
        }
        $order->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Commande annulée.']);
    }

    private function authorizeAccess(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role === 'retailer' && $order->retailer_id !== $user->id) {
            abort(403);
        }
        if ($user->role === 'wholesaler' && $order->wholesaler_id !== $user->id) {
            abort(403);
        }
    }
}
