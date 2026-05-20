<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Category extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'sort_order'];
    public array $translatable = ['name', 'slug'];

    protected $casts = [
        'is_active' => 'boolean',
        'name' => 'array',
        'slug' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }
}