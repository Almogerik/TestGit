<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20|unique:users,phone',
            'password'   => 'required|string|min:6|confirmed',
            'role'       => 'required|in:wholesaler,retailer',
            'store_name' => 'nullable|string|max:255',
            'address'    => 'nullable|string|max:255',
            'city'       => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name'       => $validated['name'],
            'phone'      => $validated['phone'],
            'password'   => Hash::make($validated['password']),
            'role'       => $validated['role'],
            'store_name' => $validated['store_name'] ?? null,
            'address'    => $validated['address'] ?? null,
            'city'       => $validated['city'] ?? 'Brazzaville',
        ]);

        $token = $user->createToken('vanda-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Identifiants incorrects.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $token = $user->createToken('vanda-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
