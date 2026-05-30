<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property array<array-key, mixed> $name
 * @property array<array-key, mixed> $slug
 * @property int|null $parent_id
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string $slug_fa
 * @property string $slug_en
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $descendants
 * @property-read int|null $descendants_count
 * @property-read array $breadcrumb
 * @property-read string $full_name
 * @property-read array $translatable_columns_from
 * @property-read string $url
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $translations
 * @method static Builder<static>|Category active()
 * @method static Builder<static>|Category newModelQuery()
 * @method static Builder<static>|Category newQuery()
 * @method static Builder<static>|Category onlyTrashed()
 * @method static Builder<static>|Category ordered()
 * @method static Builder<static>|Category parents()
 * @method static Builder<static>|Category query()
 * @method static Builder<static>|Category whereCreatedAt($value)
 * @method static Builder<static>|Category whereDeletedAt($value)
 * @method static Builder<static>|Category whereId($value)
 * @method static Builder<static>|Category whereIsActive($value)
 * @method static Builder<static>|Category whereJsonContainsLocale(string $column, string $locale, ?mixed $value, string $operand = '=')
 * @method static Builder<static>|Category whereJsonContainsLocales(string $column, array $locales, ?mixed $value, string $operand = '=')
 * @method static Builder<static>|Category whereLocale(string $column, string $locale)
 * @method static Builder<static>|Category whereLocales(string $column, array $locales)
 * @method static Builder<static>|Category whereName($value)
 * @method static Builder<static>|Category whereParentId($value)
 * @method static Builder<static>|Category whereSlug($value)
 * @method static Builder<static>|Category whereSlugEn($value)
 * @method static Builder<static>|Category whereSlugFa($value)
 * @method static Builder<static>|Category whereSortOrder($value)
 * @method static Builder<static>|Category whereUpdatedAt($value)
 * @method static Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends Model
{
    use HasTranslations, SoftDeletes;
    use HasFactory;
    
    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'sort_order'];
    
    public array $translatable = ['name', 'slug'];

    protected $casts = [
        'is_active' => 'boolean',
        'name' => 'array',
        'slug' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    public function getUrlAttribute(): string
    {
        return route($this instanceof Category ? 'category.show' : 'product.show', $this);
    }

    public function getFullNameAttribute(): string
    {
        // Build full name without N+1 (assumes parent chain is eager loaded)
        $names = [];
        $current = $this;
        
        while ($current) {
            $names[] = $current->getTranslation('name', app()->getLocale());
            $current = $current->parent;
        }
        
        return implode(' > ', array_reverse($names));
    }

    public function getBreadcrumbAttribute(): array
    {
        // Build breadcrumb without N+1 (assumes parent chain is eager loaded)
        $breadcrumb = [];
        $current = $this;
        
        while ($current) {
            $breadcrumb[] = [
                'name' => $current->name,
                'url'  => $current->url,
            ];
            $current = $current->parent;
        }
        
        return array_reverse($breadcrumb);
    }

    /**
     * Scope a query to only include active products.
     * * @param \Illuminate\Database\Eloquent\Builder<static> $query
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug_' . app()->getLocale();
    }
    
    // Boot method to handle slug synchronization for SQLite
    protected static function boot()
    {
        parent::boot();

        // Only add this event for SQLite database (to sync slug_fa and slug_en columns)
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        
        if ($driver === 'sqlite') {
            static::saving(function ($category) {
                $locales = ['fa', 'en'];
                foreach ($locales as $locale) {
                    $slugField = 'slug_' . $locale;
                    $slugValue = $category->getTranslation('slug', $locale);
                    if ($slugValue) {
                        $category->$slugField = $slugValue;
                    }
                }
            });
        }
    }
}