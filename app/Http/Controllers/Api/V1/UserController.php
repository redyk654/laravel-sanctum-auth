<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('login')->plainTextToken;

            // Ajouter le cookie de session pour l'utilisateur authentifié
            $cookie = cookie(name: 'token', value: $token, minutes: 15); // Durée de validité de 15 minutes

            return response()->json(data: [
                'token' => $token,
                'user' => $user
            ], status: 200)->withCookie($cookie);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Supprimer le cookie de session
        $cookie = cookie()->forget('token');

        return response()->json(['message' => 'Successfully logged out'])->withCookie($cookie);
    }

    public function store(RegisterUserRequest $request)
    {
        try {
            User::create($request->all());

            return response()->json([
                'status' => 200,
                'message'=> 'User créé avec succès',
            ]);
        } catch (Exception $e) {
            return response()->json($e);
        }
    }
}
