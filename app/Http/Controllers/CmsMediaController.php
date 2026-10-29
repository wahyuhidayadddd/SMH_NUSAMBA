<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CmsMediaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);
        $directory = public_path('assets/uploads');
        File::ensureDirectoryExists($directory);

        $extension = strtolower($validated['image']->guessExtension() ?? $validated['image']->extension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return response()->json(['message' => 'Format gambar tidak didukung.'], 422);
        }

        $filename = Str::uuid().'.'.$extension;
        $validated['image']->move($directory, $filename);

        return response()->json([
            'url' => asset('assets/uploads/'.$filename),
        ], 201);
    }
}
