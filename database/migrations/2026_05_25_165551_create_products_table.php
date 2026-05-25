<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Primary key
            $table->id();
            
            // Relationships
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete()
                ->index('idx_products_category');
            
            // Unique identifiers
            $table->string('sku', 100)->unique()->index('idx_products_sku');
            
            // Pricing & Inventory
            $table->decimal('price_usd', 12, 2)->index('idx_products_price');
            $table->integer('stock')->default(0)->index('idx_products_stock');
            $table->decimal('weight', 10, 3)->nullable()->comment('Weight in kg');
            
            // Multilingual JSON fields (exactly matching Category pattern)
            $table->json('name');
            $table->json('slug')->unique();
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            
            // For SQLite - fallback columns
            if (config('database.default') === 'sqlite') {
                $table->string('slug_fa', 255)->nullable()->index('idx_products_slug_fa');
                $table->string('slug_en', 255)->nullable()->index('idx_products_slug_en');
            }
            
            // Status flags
            $table->boolean('is_active')->default(true)->index('idx_products_active');
            $table->boolean('is_featured')->default(false)->index('idx_products_featured');
            
            // Sorting
            $table->integer('sort_order')->default(0)->index('idx_products_sort');
            
            // SEO
            $table->string('focus_keyword', 255)->nullable();
            
            // Timestamps & Soft Delete
            $table->timestamps();
            $table->softDeletes()->index('idx_products_deleted');
            
            // Composite indexes for common queries
            $table->index(['category_id', 'is_active', 'sort_order'], 'idx_products_category_active_sort');
            $table->index(['is_active', 'is_featured', 'deleted_at'], 'idx_products_active_featured');
            $table->index(['is_active', 'price_usd'], 'idx_products_active_price');
            $table->index(['is_active', 'stock'], 'idx_products_active_stock');
            $table->index(['created_at', 'is_active'], 'idx_products_created_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};