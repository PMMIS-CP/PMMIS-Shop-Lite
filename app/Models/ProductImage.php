<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;

/**
 * @property int $id
 * @property int $product_id
 * @property string $path
 * @property string|null $thumbnail_small
 * @property string|null $thumbnail_medium
 * @property string|null $thumbnail_large
 * @property bool $is_featured
 * @property int $sort_order
 * @property string|null $alt_text
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $large_thumbnail_url
 * @property-read string|null $medium_thumbnail_url
 * @property-read string|null $small_thumbnail_url
 * @property-read string $url
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereThumbnailLarge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereThumbnailMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereThumbnailSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereUpdatedAt($value)
 * @mixin \Illuminate\Database\Eloquent\Model
 */
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
        'sort_order'  => 'integer',
        'file_size'   => 'integer',
    ];

    /**
     * Get the product that owns the image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the S3 disk instance.
     */
    private function getDisk(): FilesystemAdapter
    {
        return Storage::disk('s3');
    }

    /**
     * Get full S3 URL for original image.
     */
    public function getUrlAttribute(): string
    {
        return $this->getDisk()->url($this->path);
    }

    /**
     * Get full S3 URL for small thumbnail.
     */
    public function getSmallThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_small ? $this->getDisk()->url($this->thumbnail_small) : null;
    }

    /**
     * Get full S3 URL for medium thumbnail.
     */
    public function getMediumThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_medium ? $this->getDisk()->url($this->thumbnail_medium) : null;
    }

    /**
     * Get full S3 URL for large thumbnail.
     */
    public function getLargeThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_large ? $this->getDisk()->url($this->thumbnail_large) : null;
    }

    /**
     * Set as featured image and reset other featured images for the same product.
     */
    public function setAsFeatured(): void
    {
        static::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_featured' => false]);

        $this->update(['is_featured' => true]);
    }

    /**
     * Delete image and all thumbnails from S3 storage.
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
            if ($this->getDisk()->exists($path)) {
                $this->getDisk()->delete($path);
            }
        }
    }

    /**
     * Bootstrap the model and handle storage cleanup on delete.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $image) {
            $image->deleteFromStorage();
        });
    }
}