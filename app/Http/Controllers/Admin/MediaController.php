<?php

namespace App\Http\Controllers\Admin;

use App\Core\Services\MediaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function index()
    {
        $basePath = storage_path('app/public');
        $files = [];

        if (File::exists($basePath)) {
            $allFiles = File::allFiles($basePath);
            foreach ($allFiles as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    $relativePath = str_replace('\\', '/', $file->getRelativePathname());
                    $files[] = [
                        'name' => $file->getFilename(),
                        'path' => $relativePath,
                        'url' => asset('storage/' . $relativePath),
                        'size' => round($file->getSize() / 1024, 1) . ' KB',
                        'modified' => $file->getMTime(),
                    ];
                }
            }
        }

        usort($files, fn($a, $b) => $b['modified'] <=> $a['modified']);

        return view('admin.media.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $this->media->uploadImage($request->file('image'), 'library');

        return back()->with('success', 'Image uploaded to Media Library.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $safePath = str_replace(['..', '\\'], '', $request->path);
        $this->media->delete($safePath);

        return back()->with('success', 'Media file deleted.');
    }
}
