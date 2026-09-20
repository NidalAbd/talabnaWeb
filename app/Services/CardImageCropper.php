<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Centre-crops an image to an exact aspect ratio and resizes it, returning
 * JPEG bytes. Used to cut the one wide AI render of a category into the
 * differently-shaped cards the app shows (hero banner, browse tile).
 */
class CardImageCropper
{
    public static function crop(string $imageBytes, int $width, int $height, int $quality = 86): string
    {
        $src = @imagecreatefromstring($imageBytes);
        if ($src === false) {
            throw new InvalidArgumentException('Bytes are not a decodable image.');
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $targetAspect = $width / $height;

        if ($srcW / $srcH > $targetAspect) {
            // Source is wider than the target: trim the sides.
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetAspect);
        } else {
            // Source is taller than the target: trim top and bottom.
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetAspect);
        }
        $cropX = intdiv($srcW - $cropW, 2);
        $cropY = intdiv($srcH - $cropH, 2);

        $dst = imagecreatetruecolor($width, $height);
        // JPEG has no alpha: flatten any transparency onto white, not black.
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $width, $height, $cropW, $cropH);

        ob_start();
        imagejpeg($dst, null, $quality);
        $jpeg = (string) ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $jpeg;
    }
}
