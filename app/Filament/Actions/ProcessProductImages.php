<?php

namespace App\Filament\Actions;

use App\Models\Product;
use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProcessProductImages
{
    /**
     * Process and upload product images using ImageUploadService.
     */
    public static function handle(Product $product, array $data): void
    {
        $imageService = app(ImageUploadService::class);
        
        // If no new images data, skip
        if (empty($data['new_images'])) {
            return;
        }
        
        // First, un-feature all images if new featured image is being set
        $hasNewFeatured = collect($data['new_images'])->contains('is_featured', true);
        
        if ($hasNewFeatured) {
            $product->images()->update(['is_featured' => false]);
        }
        
        foreach ($data['new_images'] as $imageData) {
            // Skip if no image file was uploaded
            if (empty($imageData['image'])) {
                continue;
            }
            
            try {
                $file = $imageData['image'];
                
                // Check if it's a valid file object
                if ($file instanceof UploadedFile) {
                    $imageService->upload(
                        file: $file,
                        productId: $product->id,
                        altText: $imageData['alt_text'] ?? null,
                        isFeatured: (bool) ($imageData['is_featured'] ?? false),
                    );
                } else {
                    Log::warning('Unexpected file type for product image', [
                        'product_id' => $product->id,
                        'type' => is_object($file) ? get_class($file) : gettype($file),
                        'value' => is_string($file) ? $file : 'not a string',
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('Failed to process product image: ' . $e->getMessage(), [
                    'product_id' => $product->id,
                    'exception' => $e,
                ]);
                
                throw $e;
            }
        }
    }
}