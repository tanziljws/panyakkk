<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ImageStorageService
{
    /**
     * Store image with optimization
     */
    public static function storeImage($file, $directory = 'images')
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Validasi ekstensi file
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('Format file tidak didukung');
            }
            
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . ($extension === 'png' ? 'png' : 'jpg');
            $filepath = $directory . '/' . $filename;
            
            // Process image with Intervention Image
            $image = Image::read($file->getRealPath());
            
            // Convert PNG to JPEG if not transparent
            if ($extension === 'png') {
                if (!$image->isTransparent()) {
                    $filename = str_replace('.png', '.jpg', $filename);
                    $filepath = $directory . '/' . $filename;
                    $image->toJpeg(85);
                } else {
                    $image->toPng();
                }
            } else {
                // Resize and compress JPEG/GIF
                $image->scaleDown(1920, 1920)->toJpeg(85);
            }
            
            // Save to local storage first
            $image->save(storage_path('app/public/' . $filepath));
            
            return $filename;
        } catch (\Exception $e) {
            Log::error('Image storage error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Generate thumbnail
     */
    public static function generateThumbnail($file, $directory = 'images')
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());
            $thumbnailName = 'thumb_' . time() . '_' . uniqid() . '.jpg';
            $thumbnailPath = $directory . '/' . $thumbnailName;
            
            // Process thumbnail
            $thumbnail = Image::read($file->getRealPath());
            $thumbnail->cover(400, 400)->toJpeg(80);
            
            // Save to local storage
            $thumbnail->save(storage_path('app/public/' . $thumbnailPath));
            
            return $thumbnailName;
        } catch (\Exception $e) {
            Log::error('Thumbnail generation error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete image files
     */
    public static function deleteImage($filename, $directory = 'images')
    {
        try {
            $filepath = storage_path('app/public/' . $directory . '/' . $filename);
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            // Delete thumbnail if exists
            $thumbnailPath = storage_path('app/public/' . $directory . '/thumb_' . $filename);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        } catch (\Exception $e) {
            Log::error('Image deletion error: ' . $e->getMessage());
        }
    }
}