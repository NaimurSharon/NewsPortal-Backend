<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::latest();

        $search = $request->get('search') ?: $request->get('q');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('caption', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $media = $query->paginate(30); // Increased slightly for better library feel

        // Transform collection to ensure consistency (strip legacy /storage if present)
        $media->getCollection()->transform(function($item) {
            if ($item->url) {
                // Force relative URL format without /storage
                $item->url = str_replace('/storage/', '/', $item->url);
                // Ensure no double slashes
                $item->url = '/' . ltrim($item->url, '/');
            }
            if ($item->path) {
                $item->path = str_replace('storage/', '', $item->path);
                $item->path = ltrim($item->path, '/');
            }
            return $item;
        });

        return response()->json($media);
    }

    public function store(Request $request, \App\Services\ImageService $imageService)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp,mp4,webm,ogg|max:20480', // 20MB max
                'caption' => 'nullable|string|max:255',
                'credit' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            $mimeType = $file->getClientMimeType();
            $type = str_contains($mimeType, 'video') ? 'video' : 'image';

            $path = '';
            $url = '';
            $finalMime = $mimeType;
            $size = 0;
            $dimensions = null;

            // Ensure directory exists globally
            $targetDir = base_path('media');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if ($type === 'image' && !in_array($file->getClientOriginalExtension(), ['svg', 'gif'])) {
                try {
                    // Use Service to optimize and convert to WebP
                    $path = $imageService->optimizeAndUpload($file, 'media'); // relative path: media/xyz.webp
                    $url = '/' . $path;
                    $finalMime = 'image/webp';
                    
                    // Calc size and dimensions of NEW file
                    $absPath = base_path($path);
                    if (file_exists($absPath)) {
                        $size = filesize($absPath);
                        $dims = @getimagesize($absPath);
                        $dimensions = $dims ? ['width' => $dims[0], 'height' => $dims[1]] : null;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Image optimization failed, falling back: " . $e->getMessage());
                    // Fallback: Store as is
                    $filename = $file->hashName();
                    $file->move($targetDir, $filename);
                    $path = 'media/' . $filename;
                    $url = '/' . $path;
                    $size = file_exists(base_path($path)) ? filesize(base_path($path)) : 0;
                    $dims = @getimagesize(base_path($path));
                    $dimensions = $dims ? ['width' => $dims[0], 'height' => $dims[1]] : null;
                }
            } else {
                // Store as is (Video or GIF/SVG)
                $filename = $file->hashName();
                $file->move($targetDir, $filename);
                $path = 'media/' . $filename;
                $url = '/' . $path;
                
                $absFile = base_path($path);
                if (file_exists($absFile)) {
                    $size = filesize($absFile);
                    if ($type === 'image') {
                        $dims = @getimagesize($absFile);
                        $dimensions = $dims ? ['width' => $dims[0], 'height' => $dims[1]] : null;
                    }
                }
            }

            $userId = $request->user() ? $request->user()->id : null;

            $media = Media::create([
                'filename' => basename($path),
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'url' => $url,
                'mime_type' => $finalMime,
                'size' => $size,
                'type' => $type,
                'caption' => $request->caption,
                'credit' => $request->credit,
                'dimensions' => $dimensions,
                'uploaded_by' => $userId,
            ]);

            return response()->json($media, 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Media upload 500: " . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage(),
                'type' => get_class($e)
            ], 500);
        }
    }

    public function destroy(Media $media)
    {
        try {
            // Delete the physical file directly from root media path
            if ($media->path) {
                // Remove leading slash if present to avoid double slash with base_path
                $cleanPath = ltrim($media->path, '/');
                $fullPath = base_path($cleanPath);
                
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                } else {
                     // Try legacy storage path just in case
                     if (Storage::disk('public')->exists($cleanPath)) {
                         Storage::disk('public')->delete($cleanPath);
                     }
                     // Also try public_path just in case it was there
                     $legacyPublic = public_path($cleanPath);
                     if (file_exists($legacyPublic)) {
                         unlink($legacyPublic);
                     }
                }
            }
            
            // Delete the database record
            $media->delete();
            
            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete media file: " . $e->getMessage());
            return response()->json(['error' => 'Delete failed'], 500);
        }
    }
}
