<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    /**
     * Upload, resize, and convert image to WebP
     * 
     * @param UploadedFile $file
     * @param string $folder
     * @param int $quality (0-100)
     * @return string Path to the file
     */
    public function optimizeAndUpload(UploadedFile $file, $folder = 'media', $quality = 80)
    {
        // 1. Generate Filename
        $filename = Str::random(40);
        $relativeFolder = $folder ? $folder : '';
        $targetFolder = base_path($relativeFolder);
        
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }

        // 2. Load Image Resource using GD (if available)
        if (!function_exists('imagecreatefromstring')) {
            // Fallback: move original file directly to public path if GD missing
            $ext = $file->getClientOriginalExtension();
            $fullFilename = "{$filename}.{$ext}";
            $file->move($targetFolder, $fullFilename);
            return $relativeFolder ? "{$relativeFolder}/{$fullFilename}" : $fullFilename;
        }

        $sourceImage = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        
        if (!$sourceImage) {
            // Fallback: move original file directly to public path if image loading fails
            $ext = $file->getClientOriginalExtension();
            $fullFilename = "{$filename}.{$ext}";
            $file->move($targetFolder, $fullFilename);
            return $relativeFolder ? "{$relativeFolder}/{$fullFilename}" : $fullFilename;
        }

        // 3. Resize Logic (Keep existing logic)
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $maxWidth = 1920;

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
            
            $tempImage = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($tempImage, false);
            imagesavealpha($tempImage, true);
            $transparent = imagecolorallocatealpha($tempImage, 0, 0, 0, 127);
            imagefill($tempImage, 0, 0, $transparent);
            imagecopyresampled($tempImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($sourceImage);
            $sourceImage = $tempImage;
        } else {
             imagepalettetotruecolor($sourceImage);
             imagealphablending($sourceImage, false);
             imagesavealpha($sourceImage, true);
        }

        // 5. Save as WebP directly to public path (if supported)
        if (function_exists('imagewebp')) {
            $relativePath = $relativeFolder ? "{$relativeFolder}/{$filename}.webp" : "{$filename}.webp";
            $absolutePath = base_path($relativePath);
            
            imagewebp($sourceImage, $absolutePath, $quality);
            imagedestroy($sourceImage);

            return $relativePath;
        }

        // Final fallback if imagewebp is missing
        $ext = $file->getClientOriginalExtension();
        $fullFilename = "{$filename}.{$ext}";
        $file->move($targetFolder, $fullFilename);
        imagedestroy($sourceImage);
        return $relativeFolder ? "{$relativeFolder}/{$fullFilename}" : $fullFilename;
    }
}
