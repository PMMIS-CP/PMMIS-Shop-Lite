<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function createCategory(): Category
    {
        return Category::create([
            'name' => ['fa' => 'دیجیتال', 'en' => 'Digital'],
            'slug' => ['fa' => 'digital', 'en' => 'digital'],
            'is_active' => true,
        ]);
    }

    private function getProductData(Category $category, array $overrides = []): array
    {
        return array_merge([
            'name' => ['fa' => 'لپ تاپ', 'en' => 'Laptop'],
            'price_usd' => 1000,
            'category_id' => $category->id,
            'sku' => 'SKU-' . uniqid(),
            'is_active' => true,
        ], $overrides);
    }

    public function test_slug_generation_integrity(): void
    {
        $cat = $this->createCategory();
        $p1 = Product::create($this->getProductData($cat))->fresh();
        $p2 = Product::create($this->getProductData($cat))->fresh();

        $this->assertNotNull($p1->getTranslation('slug', 'fa'));
        $this->assertNotEquals($p1->getTranslation('slug', 'fa'), $p2->getTranslation('slug', 'fa'));
    }

    public function test_product_stock_lifecycle(): void
    {
        $cat = $this->createCategory();
        $p = Product::create($this->getProductData($cat, ['stock' => 5]))->fresh();

        $this->assertTrue($p->isPurchasable());
        $this->assertTrue($p->decreaseStock(2));
        $this->assertEquals(3, $p->fresh()->stock);
        $this->assertFalse($p->decreaseStock(10));
    }

    public function test_product_price_calculations(): void
    {
        $cat = $this->createCategory();
        $p = Product::create($this->getProductData($cat, ['price_usd' => 100]))->fresh();

        $this->assertGreaterThan(0, $p->price_irr);
        $this->assertStringContainsString('$', $p->formatted_price);
    }

    public function test_scopes_and_filtering(): void
    {
        $cat = $this->createCategory();
        
        Product::create($this->getProductData($cat, ['is_active' => true, 'stock' => 5]));
        
        Product::create($this->getProductData($cat, ['is_active' => false, 'stock' => 0]));

        $this->assertEquals(1, Product::active()->count());
        $this->assertEquals(1, Product::inStock()->count());
    }
}