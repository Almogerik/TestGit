<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WholesalerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query  = User::where('role', 'wholesaler')->where('is_active', true)
            ->select('id', 'name', 'store_name', 'address', 'city', 'phone');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('store_name', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function connect(Request $request, User $wholesaler)
    {
        if ($request->user()->role !== 'retailer') {
            abort(403, 'Réservé aux détaillants.');
        }
        if ($wholesaler->role !== 'wholesaler') {
            abort(422, "Cet utilisateur n'est pas un grossiste.");
        }

        $existing = $request->user()->wholesalers()->where('wholesaler_id', $wholesaler->id)->first();
        if ($existing) {
            return response()->json(['message' => 'Déjà connecté ou demande en attente.', 'status' => $existing->pivot->status]);
        }

        $request->user()->wholesalers()->attach($wholesaler->id, ['status' => 'pending']);

        return response()->json(['message' => 'Demande de connexion envoyée.'], 201);
    }

    public function approveRetailer(Request $request, User $retailer)
    {
        if ($request->user()->role !== 'wholesaler') {
            abort(403, 'Réservé aux grossistes.');
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $request->user()->retailers()->updateExistingPivot($retailer->id, [
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Statut mis à jour.']);
    }

    public function myRetailers(Request $request)
    {
        if ($request->user()->role !== 'wholesaler') {
            abort(403);
        }
        $retailers = $request->user()->retailers()
            ->wherePivot('status', 'approved')
            ->select('users.id', 'users.name', 'users.store_name', 'users.phone', 'users.address')
            ->get();

        return response()->json($retailers);
    }

    public function myWholesalers(Request $request)
    {
        if ($request->user()->role !== 'retailer') {
            abort(403);
        }
        $wholesalers = $request->user()->wholesalers()
            ->wherePivot('status', 'approved')
            ->select('users.id', 'users.name', 'users.store_name', 'users.phone', 'users.address')
            ->get();

        return response()->json($wholesalers);
    }
}

