<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Intervention\Image\Interfaces\ImageInterface;

class ImageUploadService
{
    protected ImageManager $manager;
    
    // Thumbnail dimensions [width, height]
    protected const THUMBNAILS = [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [800, 800],
    ];
    
    protected const MAX_FILE_SIZE = 2048; // 2MB in KB
    protected const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    protected const WEBP_QUALITY = 75;
    
    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }
    
    /**
     * Upload a product image directly to S3 without local disk write.
     */
    public function upload(UploadedFile $file, int $productId, ?string $altText = null, bool $isFeatured = false): ProductImage
    {
        // Validate file
        $this->validateFile($file);
        
        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $uniqueId = Str::random(16);
        $timestamp = now()->format('Y/m/d');
        
        // Read image from uploaded file (stream directly)
        $image = $this->manager->decode($file->getPathname());
        
        // Upload original image (convert to WebP)
        $originalPath = $this->uploadOriginal($image, $productId, $timestamp, $uniqueId);
        
        // Generate and upload thumbnails
        $thumbnails = $this->uploadThumbnails($image, $productId, $timestamp, $uniqueId);
        
        // Create database record
        return ProductImage::create([
            'product_id' => $productId,
            'path' => $originalPath,
            'thumbnail_small' => $thumbnails['small'] ?? null,
            'thumbnail_medium' => $thumbnails['medium'] ?? null,
            'thumbnail_large' => $thumbnails['large'] ?? null,
            'is_featured' => $isFeatured,
            'alt_text' => $altText ?? $originalName,
            'mime_type' => 'image/webp',
            'file_size' => $file->getSize(),
        ]);
    }
    
    /**
     * Upload original image as WebP.
     */
    protected function uploadOriginal(ImageInterface $image, int $productId, string $timestamp, string $uniqueId): string
    {
        // Encode as WebP with specified quality
        $encodedImage = $image->encodeUsingFileExtension('webp', quality: self::WEBP_QUALITY);

        
        $path = "products/{$productId}/{$timestamp}/{$uniqueId}_original.webp";
        
        Storage::disk('s3')->put($path, (string) $encodedImage, [
            'visibility' => 'public',
            'ContentType' => 'image/webp',
        ]);
        
        return $path;
    }
    
    /**
     * Generate and upload thumbnails.
     */
    protected function uploadThumbnails(ImageInterface $image, int $productId, string $timestamp, string $uniqueId): array
    {
        $thumbnails = [];
        
        foreach (self::THUMBNAILS as $size => [$width, $height]) {
            // Clone the image to avoid affecting the original object
            $thumbnail = clone $image;
            // Resize and crop to the exact dimensions
            $thumbnail->cover($width, $height);
            
            // Encode the thumbnail as WebP
            $encodedThumb = $thumbnail->encodeUsingFileExtension('webp', quality: self::WEBP_QUALITY);

            
            $path = "products/{$productId}/{$timestamp}/{$uniqueId}_{$size}.webp";
            
            Storage::disk('s3')->put($path, (string) $encodedThumb, [
                'visibility' => 'public',
                'ContentType' => 'image/webp',
            ]);
            
            $thumbnails[$size] = $path;
        }
        
        return $thumbnails;
    }
    
    /**
     * Delete an image and all its thumbnails from S3.
     */
    public function delete(ProductImage $image): bool
    {
        $paths = array_filter([
            $image->path,
            $image->thumbnail_small,
            $image->thumbnail_medium,
            $image->thumbnail_large,
        ]);
        
        $deleted = true;
        
        foreach ($paths as $path) {
            if (Storage::disk('s3')->exists($path)) {
                $deleted = $deleted && Storage::disk('s3')->delete($path);
            }
        }
        
        if ($deleted) {
            $image->delete();
        }
        
        return $deleted;
    }
    
    /**
     * Update image sort order.
     */
    public function updateSortOrder(ProductImage $image, int $newOrder): void
    {
        $image->update(['sort_order' => $newOrder]);
    }
    
    /**
     * Set featured image for a product.
     */
    public function setFeatured(ProductImage $image): void
    {
        $image->setAsFeatured();
    }
    
    /**
     * Validate uploaded file.
     *
     * @throws \InvalidArgumentException
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            throw new \InvalidArgumentException(
                sprintf('File size must be less than %d KB', self::MAX_FILE_SIZE)
            );
        }
        
        // Check mime type
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            throw new \InvalidArgumentException(
                sprintf('File type not allowed. Allowed: %s', implode(', ', self::ALLOWED_MIMES))
            );
        }
        
        // Check if file is a valid image
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Invalid or corrupted image file');
        }
    }
}