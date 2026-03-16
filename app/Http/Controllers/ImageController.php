<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Serves optimized images (WebP) for the web frontend.
 * Original images in storage remain untouched — Flutter data is safe.
 * WebP copies are cached separately in storage/app/public/cache/webp/
 */
class ImageController extends Controller
{
    public function serve(Request $request, string $path)
    {
        // Security: prevent directory traversal
        $path = str_replace(['..', "\0"], '', $path);

        $originalPath = storage_path("app/public/{$path}");

        if (!file_exists($originalPath)) {
            abort(404);
        }

        // Check if browser accepts WebP
        $acceptsWebP = str_contains($request->header('Accept', ''), 'image/webp');

        if ($acceptsWebP && extension_loaded('gd')) {
            $webpPath = storage_path("app/public/cache/webp/{$path}.webp");

            // Serve cached WebP if it exists
            if (file_exists($webpPath)) {
                return response()->file($webpPath, [
                    'Content-Type' => 'image/webp',
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                    'Vary' => 'Accept',
                ]);
            }

            // Try to convert to WebP
            $info = @getimagesize($originalPath);
            if ($info) {
                $image = match ($info['mime']) {
                    'image/jpeg' => @imagecreatefromjpeg($originalPath),
                    'image/png' => @imagecreatefrompng($originalPath),
                    default => null,
                };

                if ($image) {
                    $dir = dirname($webpPath);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    // Resize if too large (max 800px wide for listings)
                    $width = imagesx($image);
                    $height = imagesy($image);
                    $maxWidth = 800;

                    if ($width > $maxWidth) {
                        $newHeight = (int) ($height * ($maxWidth / $width));
                        $resized = imagecreatetruecolor($maxWidth, $newHeight);

                        // Preserve transparency for PNG
                        if ($info['mime'] === 'image/png') {
                            imagealphablending($resized, false);
                            imagesavealpha($resized, true);
                        }

                        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                        imagedestroy($image);
                        $image = $resized;
                    }

                    imagewebp($image, $webpPath, 80);
                    imagedestroy($image);

                    if (file_exists($webpPath)) {
                        return response()->file($webpPath, [
                            'Content-Type' => 'image/webp',
                            'Cache-Control' => 'public, max-age=31536000, immutable',
                            'Vary' => 'Accept',
                        ]);
                    }
                }
            }
        }

        // Fallback: serve original with cache headers
        return response()->file($originalPath, [
            'Cache-Control' => 'public, max-age=31536000',
            'Vary' => 'Accept',
        ]);
    }
}
