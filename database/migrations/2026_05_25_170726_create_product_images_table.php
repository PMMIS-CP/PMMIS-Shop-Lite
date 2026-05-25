<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->index('idx_product_images_product');
            
            // S3 paths
            $table->string('path', 500)->comment('Original image S3 path');
            $table->string('thumbnail_small', 500)->nullable()->comment('150x150 S3 path');
            $table->string('thumbnail_medium', 500)->nullable()->comment('300x300 S3 path');
            $table->string('thumbnail_large', 500)->nullable()->comment('800x800 S3 path');
            
            $table->boolean('is_featured')->default(false)->index('idx_product_images_featured');
            $table->integer('sort_order')->default(0);
            $table->string('alt_text', 255)->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->integer('file_size')->nullable()->comment('Size in bytes');
            
            $table->timestamps();
            
            // Composite index
            $table->index(['product_id', 'is_featured', 'sort_order'], 'idx_product_images_product_featured_sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};