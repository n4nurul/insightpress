<?php

namespace App\Core\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * ছবি আপলোড করে অটোমেটিক রিসাইজ এবং WebP ফরম্যাটে সেভ করা (cPanel GD friendly)
     */
    public function uploadImage(UploadedFile $file, string $folder = 'uploads', int $maxWidth = 1200, int $quality = 82): string
    {
        $filename = date('Y/m/') . Str::random(20);
        $disk = Storage::disk('public');
        $disk->makeDirectory($folder . '/' . date('Y/m'));

        $sourcePath = $file->getRealPath();
        $mime = $file->getMimeType();

        // GD দিয়ে ইমেজ লোড করা
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        // যদি WebP সাপোর্ট না থাকে বা GIF/SVG হয় তবে সরাসরি সেভ করবে
        if (!$image || !function_exists('imagewebp')) {
            return $file->store($folder . '/' . date('Y/m'), 'public');
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($origHeight / $origWidth) * $newWidth);
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($image);
            $image = $resized;
        }

        $relativePath = "{$folder}/{$filename}.webp";
        $fullPath = $disk->path($relativePath);

        imagewebp($image, $fullPath, $quality);
        imagedestroy($image);

        return $relativePath;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
