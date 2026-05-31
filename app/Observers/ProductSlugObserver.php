<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductSlugObserver
{
    public function creating(Product $model): void
    {
        if (empty($model->sku)) {
            $model->sku = $this->generateUniqueSku();
        }

        $this->generateSlugs($model);
    }

    public function updating(Product $model): void
    {
        if ($model->isDirty('name') && !$model->isDirty('slug')) {
            $this->generateSlugs($model);
        }
    }


    private function generateUniqueSku(): string
    {
        $lastProduct = Product::whereNotNull('sku')
            ->orderBy('sku', 'desc')
            ->first();

        if (!$lastProduct) {
            return '00AAAAA';
        }

        $lastSku = $lastProduct->sku; // مثلاً 00AAAAB
        
        $numbers = substr($lastSku, 0, 2);
        $letters = substr($lastSku, 2, 5);

        $letterVal = 0;
        for ($i = 0; $i < 5; $i++) {
            $letterVal = $letterVal * 26 + (ord($letters[$i]) - ord('A'));
        }

        $letterVal++;

        if ($letterVal >= pow(26, 5)) {
            $letterVal = 0;
            $numbers = str_pad((int)$numbers + 1, 2, '0', STR_PAD_LEFT);
        }

        $newLetters = '';
        for ($i = 4; $i >= 0; $i--) {
            $newLetters = chr(($letterVal % 26) + ord('A')) . $newLetters;
            $letterVal = floor($letterVal / 26);
        }

        return $numbers . $newLetters;
    }

    private function generateSlugs(Product $model): void
    {
        $names = (array) $model->name;
        if (empty($names)) return;

        $newSlugs = (array) $model->slug;

        foreach (array_keys($names) as $locale) {
            $sourceText = $names[$locale] ?? null;
            if (!$sourceText) continue;
            
            $slug = ($locale === 'fa') 
                ? $this->generatePersianSlug($sourceText) 
                : Str::slug($sourceText);

            $originalSlug = $slug;
            $count = 1;
            
            while (Product::where("slug->{$locale}", $slug)
                ->where('id', '!=', $model->id ?? 0)
                ->exists()
            ) {
                $slug = $originalSlug . '-' . $count++;
            }

            $newSlugs[$locale] = $slug;
        }

        $model->slug = $newSlugs;
    }

    private function generatePersianSlug(string $string): string
    {
        $string = $this->convertPersianNumbersToEnglish($string); 
        $string = mb_strtolower($string, 'UTF-8');
        $string = str_replace(['‌', ' '], '-', $string);
        $string = preg_replace('/[^a-z0-9\x{0621}-\x{06CC}\-]+/u', '', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }

    private function convertPersianNumbersToEnglish(string $string): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $english, $string);
    }
}