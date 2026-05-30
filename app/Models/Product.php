<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Database\Eloquent\Builder;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $category_id
 * @property string $sku
 * @property float $price_usd
 * @property int $stock
 * @property float|null $weight
 * @property array<array-key, mixed> $name
 * @property array<array-key, mixed> $slug
 * @property array<array-key, mixed>|null $short_description
 * @property array<array-key, mixed>|null $description
 * @property array<array-key, mixed>|null $meta_title
 * @property array<array-key, mixed>|null $meta_description
 * @property string|null $slug_fa
 * @property string|null $slug_en
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property string|null $focus_keyword
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\ProductImage|null $featuredImage
 * @property-read string $formatted_price
 * @property-read bool $is_in_stock
 * @property-read string|null $localized_description
 * @property-read string $localized_name
 * @property-read string|null $localized_short_description
 * @property-read float $price_irr
 * @property-read string $stock_status
 * @property-read string $stock_status_badge
 * @property-read array $translatable_columns_from
 * @property-read string $url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductImage> $images
 * @property-read int|null $images_count
 * @property-read mixed $translations
 * @method static Builder<static>|Product active()
 * @method static Builder<static>|Product byCategory(int $categoryId)
 * @method static Builder<static>|Product featured()
 * @method static Builder<static>|Product inStock()
 * @method static Builder<static>|Product lowStock(int $threshold = 10)
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product orderByPrice(string $direction = 'asc')
 * @method static Builder<static>|Product outOfStock()
 * @method static Builder<static>|Product priceRange(float $min, float $max)
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product search(?string $searchTerm)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereFocusKeyword($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereIsActive($value)
 * @method static Builder<static>|Product whereIsFeatured($value)
 * @method static Builder<static>|Product whereJsonContainsLocale(string $column, string $locale, ?mixed $value, string $operand = '=')
 * @method static Builder<static>|Product whereJsonContainsLocales(string $column, array $locales, ?mixed $value, string $operand = '=')
 * @method static Builder<static>|Product whereLocale(string $column, string $locale)
 * @method static Builder<static>|Product whereLocales(string $column, array $locales)
 * @method static Builder<static>|Product whereMetaDescription($value)
 * @method static Builder<static>|Product whereMetaTitle($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePriceUsd($value)
 * @method static Builder<static>|Product whereShortDescription($value)
 * @method static Builder<static>|Product whereSku($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereSlugEn($value)
 * @method static Builder<static>|Product whereSlugFa($value)
 * @method static Builder<static>|Product whereSortOrder($value)
 * @method static Builder<static>|Product whereStock($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product whereWeight($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasTranslations, SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'sku',
        'stock',
        'price_usd',
        'weight',
        'sort_order',
        'is_active',
        'is_featured',
        'focus_keyword',
        // Multilingual fields
        'name',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that are translatable.
     */
    public array $translatable = [
        'name',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_usd' => 'decimal:2',
        'weight' => 'decimal:3',
        'stock' => 'integer',
        'sort_order' => 'integer',
        'name' => 'array',
        'slug' => 'array',
        'short_description' => 'array',
        'description' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'formatted_price',
        'is_in_stock',
        'stock_status',
        'url',
    ];

    /**
     * Model event listeners.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Consolidated cache clearing
        static::saved(function ($product) {
            Cache::forget("product_{$product->id}");
            Cache::tags(['products'])->flush();
        });

        static::deleted(function () {
            Cache::tags(['products'])->flush();
        });

        // SQLite compatibility: sync slug_* columns
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        
        if ($driver === 'sqlite') {
            static::saving(function ($product) {
                $locales = ['fa', 'en'];
                foreach ($locales as $locale) {
                    $slugField = 'slug_' . $locale;
                    $slugValue = $product->getTranslation('slug', $locale);
                    if ($slugValue) {
                        $product->$slugField = $slugValue;
                    }
                }
            });
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault();
    }

    /**
     * Get all images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Get the featured image for the product.
     */
    public function featuredImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_featured', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */


    /**
     * Get formatted price with currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        $currency = app(\Illuminate\Session\Store::class)->get('currency', 'USD');
        
        if ($currency === 'IRR') {
            return number_format($this->price_irr) . ' ' . __('rial');
        }
        
        return number_format($this->price_usd, 2) . ' $';
    }

    /**
     * Get price in Iranian Rial (converted from USD).
     */
    public function getPriceIrrAttribute(): float
    {
        // Cache exchange rate for 1 hour
        $exchangeRate = Cache::remember('usd_to_irr_rate', 3600, function () {
            // You can implement real API call here
            // For now, using config or default rate
            return config('currency.usd_to_irr', 585000);
        });
        
        return round($this->price_usd * $exchangeRate);
    }

    /**
     * Check if product is in stock.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Get stock status text.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out_of_stock';
        }
        
        if ($this->stock < 10) {
            return 'low_stock';
        }
        
        return 'in_stock';
    }

    /**
     * Get stock status badge class.
     */
    public function getStockStatusBadgeAttribute(): string
    {
        return match($this->stock_status) {
            'in_stock' => 'badge-success',
            'low_stock' => 'badge-warning',
            'out_of_stock' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Get product URL with current locale slug.
     */
    public function getUrlAttribute(): string 
    { 
        return route('product.show', $this); 
    }

    /**
     * Get product name with fallback.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        $name = $this->getTranslation('name', $locale);
        
        if (empty($name) && $locale !== 'en') {
            $name = $this->getTranslation('name', 'en');
        }
        
        return $name ?? 'Unnamed Product';
    }

    /**
     * Get short description with fallback.
     */
    public function getLocalizedShortDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $description = $this->getTranslation('short_description', $locale);
        
        if (empty($description) && $locale !== 'en') {
            $description = $this->getTranslation('short_description', 'en');
        }
        
        return $description;
    }

    /**
     * Get description with fallback.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        $description = $this->getTranslation('description', $locale);
        
        if (empty($description) && $locale !== 'en') {
            $description = $this->getTranslation('description', 'en');
        }
        
        return $description;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active products.
     * * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include low stock products.
     */
    public function scopeLowStock(Builder $query, int $threshold = 10): Builder
    {
        return $query->where('stock', '>', 0)->where('stock', '<', $threshold);
    }

    /**
     * Scope a query to only include out of stock products.
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock', '<=', 0);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price_usd', [$min, $max]);
    }

    /**
     * Scope a query to order by price.
     */
    public function scopeOrderByPrice(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('price_usd', $direction);
    }

    /**
     * Scope a query for search (using Laravel Scout or basic LIKE).
     */
    public function scopeSearch(Builder $query, ?string $searchTerm): Builder
    {
        if (empty($searchTerm)) {
            return $query;
        }

        $locale = app()->getLocale();
        
        return $query->where(function ($q) use ($searchTerm, $locale) {
            $q->where("name->{$locale}", 'LIKE', "%{$searchTerm}%")
              ->orWhere('sku', 'LIKE', "%{$searchTerm}%");
              
            if ($locale !== 'en') {
                $q->orWhere("name->en", 'LIKE', "%{$searchTerm}%");
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Decrease product stock.
     */
    public function decreaseStock(int $quantity = 1): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }
        
        $this->decrement('stock', $quantity);
        return true;
    }

    /**
     * Increase product stock.
     */
    public function increaseStock(int $quantity = 1): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Check if product can be purchased.
     */
    public function isPurchasable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Get related products from same category.
     */
    public function getRelatedProducts(int $limit = 4): \Illuminate\Support\Collection
    {
        return self::active()
            ->where('id', '!=', $this->id)
            ->where('category_id', $this->category_id)
            ->inStock()
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
    public function getRouteKeyName(): string
    {
        return 'slug_' . app()->getLocale();
    }
}