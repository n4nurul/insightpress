<?php

namespace App\Http\Controllers\Admin;

use App\Core\Services\MediaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditorUploadController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $path = $this->media->uploadImage($request->file('image'), 'editor');

        return response()->json([
            'url' => asset('storage/' . $path),
        ]);
    }
}
