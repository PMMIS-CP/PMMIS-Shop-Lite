<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;


class ProductImage extends Model
{
    protected $table = 'product_images';
    
    protected $fillable = [
        'product_id',
        'path',
        'thumbnail_small',
        'thumbnail_medium',
        'thumbnail_large',
        'is_featured',
        'sort_order',
        'alt_text',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the product that owns the image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get full S3 URL for original image.
     */
    public function getUrlAttribute(): string
    {
        return app('filesystem')->disk('s3')->url($this->path);
    }

    /**
     * Get full S3 URL for small thumbnail.
     */
    public function getSmallThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_small) {
            return null;
        }
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');
        return $disk->url($this->thumbnail_small);
    }

    /**
     * Get full S3 URL for medium thumbnail.
     */
    public function getMediumThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_medium) {
            return null;
        }
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');
        return $disk->url($this->thumbnail_medium);
    }

    /**
     * Get full S3 URL for large thumbnail.
     */
    public function getLargeThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_large) {
            return null;
        }
        
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');
        return $disk->url($this->thumbnail_large);
    }

    /**
     * Set as featured image (removes other featured images for this product).
     */
    public function setAsFeatured(): void
    {
        static::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_featured' => false]);
        
        $this->update(['is_featured' => true]);
    }

    /**
     * Delete image and all thumbnails from S3.
     */
    public function deleteFromStorage(): void
    {
        $paths = array_filter([
            $this->path,
            $this->thumbnail_small,
            $this->thumbnail_medium,
            $this->thumbnail_large,
        ]);
        
        foreach ($paths as $path) {
            if ($path && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
        }
    }

    /**
     * Delete model and remove files from storage.
     */
    protected static function booted(): void
    {
        static::deleting(function ($image) {
            $image->deleteFromStorage();
        });
    }
}