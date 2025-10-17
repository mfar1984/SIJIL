<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function tinymceImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,webp,gif|max:4096',
        ]);

        $path = $request->file('file')->store('editor/images', 'public');
        $url = asset('storage/' . $path);

        return response()->json(['location' => $url]);
    }
}


