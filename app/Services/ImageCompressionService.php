<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageCompressionService
{
    public static function compressInPlace(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $supportedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        // If it's not a supported image, do nothing
        if (!str_starts_with($mimeType, 'image/') || !in_array($extension, $supportedExtensions)) {
            \Illuminate\Support\Facades\Log::info("ImageCompressionService: Skipped compression for non-supported file type: $mimeType, ext: $extension");
            return false;
        }

        try {
            $tempPath = $file->getRealPath();
            $originalSize = filesize($tempPath);
            \Illuminate\Support\Facades\Log::info("ImageCompressionService: Starting compression for $extension. Original size: " . round($originalSize / 1024) . " KB");
            $image = null;

            if ($extension === 'jpeg' || $extension === 'jpg') {
                $image = @imagecreatefromjpeg($tempPath);
            } elseif ($extension === 'png') {
                $image = @imagecreatefrompng($tempPath);
            } elseif ($extension === 'webp') {
                $image = @imagecreatefromwebp($tempPath);
            }

            if (!$image) {
                return false;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            $maxWidth = 1920;
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($height * ($maxWidth / $width));
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            if ($extension === 'png' || $extension === 'webp') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save compressed image directly over the original temp file
            if ($extension === 'jpeg' || $extension === 'jpg') {
                imagejpeg($newImage, $tempPath, 75);
            } elseif ($extension === 'png') {
                imagepng($newImage, $tempPath, 7);
            } elseif ($extension === 'webp') {
                imagewebp($newImage, $tempPath, 75);
            }

            imagedestroy($image);
            imagedestroy($newImage);
            
            // Clear stat cache so filesize() returns the new compressed size
            clearstatcache(true, $tempPath);
            $newSize = filesize($tempPath);
            \Illuminate\Support\Facades\Log::info("ImageCompressionService: Compression successful. New size: " . round($newSize / 1024) . " KB");

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("ImageCompressionService: Compression failed - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compresses the uploaded file if it's an image, and stores it.
     * If it's a PDF or other document, it simply stores it normally to avoid corruption.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @param string|null $filename
     * @return false|string
     */
    public static function compressAndStore(UploadedFile $file, $path, $disk = 'public', $filename = null)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        // If no filename is provided, Laravel's default store() behavior creates a random hash
        if (!$filename) {
            $filename = $file->hashName();
        } else {
            // Ensure filename has the correct extension if passed manually
            if (!str_ends_with($filename, '.' . $extension)) {
                $filename .= '.' . $extension;
            }
        }

        // Compress the file in place first
        self::compressInPlace($file);

        // Then just use Laravel's native storeAs, which will read the newly compressed temp file
        return $file->storeAs($path, $filename, $disk);
    }
}
