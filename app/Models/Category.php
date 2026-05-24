<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'sort_order'];
    
    public array $translatable = ['name', 'slug'];

    protected $casts = [
        'is_active' => 'boolean',
        'name' => 'array',
        'slug' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    // Temporarily commented until Product model exists
    // public function products(): HasMany
    // {
    //     return $this->hasMany(Product::class);
    // }

    public function getUrlAttribute(): string
    {
        $locale = app()->getLocale();
        $slug = $this->getTranslation('slug', $locale);
        
        return route('category.show', $slug);
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