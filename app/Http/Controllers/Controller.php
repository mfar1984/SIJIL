<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
        View::share('firebasePublic', [
            'apiKey' => env('VITE_FIREBASE_API_KEY'),
            'authDomain' => env('VITE_FIREBASE_AUTH_DOMAIN'),
            'projectId' => env('VITE_FIREBASE_PROJECT_ID'),
            'storageBucket' => env('VITE_FIREBASE_STORAGE_BUCKET'),
            'messagingSenderId' => env('VITE_FIREBASE_MESSAGING_SENDER_ID'),
            'appId' => env('VITE_FIREBASE_APP_ID'),
            'measurementId' => env('VITE_FIREBASE_MEASUREMENT_ID'),
            'vapidKey' => env('VITE_FIREBASE_VAPID_KEY'),
        ]);
    }
}
