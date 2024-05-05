<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MusadaqaController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token, 'user' => auth()->user()], 200);
    }

        // public function login(Request $request)
        
}
