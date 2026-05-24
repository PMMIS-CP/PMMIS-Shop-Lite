<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // اضافه کردن کلید خارجی parent_id (برای همه دیتابیس‌ها کار می‌کند)
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });

        // تشخیص نوع دیتابیس و اجرای کد مناسب
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // برای MySQL/MariaDB: استفاده از ستون‌های مجازی (virtual generated columns)
            DB::statement('ALTER TABLE categories ADD COLUMN slug_fa VARCHAR(255) GENERATED ALWAYS AS (JSON_UNQUOTE(slug->>"$.fa")) STORED');
            DB::statement('ALTER TABLE categories ADD COLUMN slug_en VARCHAR(255) GENERATED ALWAYS AS (JSON_UNQUOTE(slug->>"$.en")) STORED');
            
            // اضافه کردن ایندکس و unique constraint
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug_fa');
                $table->index('slug_en');
                $table->unique('slug_fa');
                $table->unique('slug_en');
            });
        } 
        elseif ($driver === 'sqlite') {
            // برای SQLite: ستون‌های معمولی اضافه می‌کنیم (چون GENERATED COLUMN پشتیبانی نمی‌شود)
            // ابتدا ستون‌های جدید اضافه می‌شوند (بدون داده)
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug_fa')->nullable()->after('slug');
                $table->string('slug_en')->nullable()->after('slug_fa');
            });
            
            // سپس داده‌های موجود را به‌روزرسانی می‌کنیم
            $categories = DB::table('categories')->get();
            foreach ($categories as $category) {
                $slugData = json_decode($category->slug, true);
                $slugFa = $slugData['fa'] ?? null;
                $slugEn = $slugData['en'] ?? null;
                
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update([
                        'slug_fa' => $slugFa,
                        'slug_en' => $slugEn,
                    ]);
            }
            
            // ستون‌ها را NOT NULL می‌کنیم (بعد از پر کردن داده)
            Schema::table('categories', function (Blueprint $table) {
                $table->string('slug_fa')->nullable(false)->change();
                $table->string('slug_en')->nullable(false)->change();
            });
            
            // اضافه کردن ایندکس و unique constraint
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug_fa');
                $table->index('slug_en');
                $table->unique('slug_fa');
                $table->unique('slug_en');
            });
        }
        elseif ($driver === 'pgsql') {
            // برای PostgreSQL (اگر نیاز شد)
            DB::statement('ALTER TABLE categories ADD COLUMN slug_fa VARCHAR(255) GENERATED ALWAYS AS (slug->>"fa") STORED');
            DB::statement('ALTER TABLE categories ADD COLUMN slug_en VARCHAR(255) GENERATED ALWAYS AS (slug->>"en") STORED');
            
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug_fa');
                $table->index('slug_en');
                $table->unique('slug_fa');
                $table->unique('slug_en');
            });
        }
    }

    public function down(): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        // حذف کلید خارجی
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // حذف ایندکس‌ها و unique constraint برای MySQL
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['slug_fa']);
                $table->dropIndex(['slug_en']);
                $table->dropUnique(['slug_fa']);
                $table->dropUnique(['slug_en']);
            });
            
            // حذف ستون‌های مجازی
            DB::statement('ALTER TABLE categories DROP COLUMN slug_fa');
            DB::statement('ALTER TABLE categories DROP COLUMN slug_en');
        }
        elseif ($driver === 'sqlite') {
            // حذف ایندکس‌ها برای SQLite
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['slug_fa']);
                $table->dropIndex(['slug_en']);
                $table->dropUnique(['slug_fa']);
                $table->dropUnique(['slug_en']);
            });
            
            // حذف ستون‌های معمولی (SQLite محدودیت دارد، باید جدول را دوباره بسازیم)
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn(['slug_fa', 'slug_en']);
            });
        }
        elseif ($driver === 'pgsql') {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['slug_fa']);
                $table->dropIndex(['slug_en']);
                $table->dropUnique(['slug_fa']);
                $table->dropUnique(['slug_en']);
                $table->dropColumn(['slug_fa', 'slug_en']);
            });
        }
    }
};