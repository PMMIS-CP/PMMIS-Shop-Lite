<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Str;

class SeoSlugObserver
{
    public function creating(Category $model): void
    {
        $this->generateSlugs($model);
    }

    public function updating(Category $model): void
    {
        // Only regenerate if name changed AND slug was not manually modified
        if ($model->isDirty('name') && !$model->isDirty('slug')) {
            $this->generateSlugs($model);
        }
    }

    private function generateSlugs(Category $model): void
    {
        $locales = array_keys($model->getTranslations('name'));

        foreach ($locales as $locale) {
            $sourceText = $model->getTranslation('name', $locale, false); 
            if (!$sourceText) continue;
            
            $slug = ($locale === 'fa') 
                ? $this->generatePersianSlug($sourceText) 
                : Str::slug($sourceText);

            $originalSlug = $slug;
            $count = 1;
            
            while (Category::where('slug->' . $locale, $slug)
                ->where('id', '!=', $model->id)
                ->exists()
            ) {
                $slug = $originalSlug . '-' . $count++;
            }

            $model->setTranslation('slug', $locale, $slug);
        }
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