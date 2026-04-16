<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        auth()->user()->update([
            'fcm_token' => $request->token
        ]);

        return response()->json(['message' => 'Token actualizado']);
    }
}
