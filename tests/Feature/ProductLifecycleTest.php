<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductLifecycleTest extends TestCase
{
    use RefreshDatabase; 

    #[Test]
    public function it_can_manage_product_lifecycle_correctly(): void
    {
        $category = Category::create([
            'name' => ['fa' => 'تست', 'en' => 'Test'],
            'slug' => ['fa' => 'test', 'en' => 'test'],
            'is_active' => true
        ]);

        $product = Product::create([
            'name' => ['fa' => 'محصول تست', 'en' => 'Test Product'],
            'price_usd' => 10.00,
            'category_id' => $category->id,
            'sku' => 'SKU-' . uniqid(),
            'is_active' => true,
            'stock' => 10
        ]);

        $this->assertTrue($product->isPurchasable(), 'محصول باید قابل خرید باشد');
        
        $result = $product->decreaseStock(1);
        
        $this->assertTrue($result, 'کاهش موجودی باید موفقیت‌آمیز باشد');
        $this->assertEquals(9, $product->fresh()->stock, 'موجودی انبار باید به ۹ کاهش یابد');
    }

    #[Test]
    public function it_prevents_purchasing_out_of_stock_products(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        
        $this->assertFalse($product->isPurchasable(), 'محصول بدون موجودی نباید قابل خرید باشد');
    }
}