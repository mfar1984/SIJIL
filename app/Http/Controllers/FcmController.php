<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Registering FCM token

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => $user->id, 'device' => $request->header('User-Agent'), 'last_used_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function unregister(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);
        // Unregistering FCM token
        FcmToken::where('token', $request->token)->delete();
        return response()->json(['success' => true]);
    }
}


