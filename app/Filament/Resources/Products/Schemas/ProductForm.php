<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('اطلاعات پایه')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                        TextInput::make('sku')->label('SKU')->required(),
                        TextInput::make('price_usd')->required()->numeric()->prefix('$'),
                        TextInput::make('stock')->required()->numeric()->default(0),
                        TextInput::make('weight')->numeric(),
                    ])->columns(3),

                Section::make('محتوای چندزبانه')
                    ->schema([
                        TextInput::make('name')
                            ->label('نام محصول')
                            ->required()
                            ->translatable() 
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('نامک (Slug)')
                            ->required()
                            ->translatable()
                            ->columnSpanFull(),

                        Textarea::make('short_description')
                            ->label('توضیحات کوتاه')
                            ->translatable()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('توضیحات کامل')
                            ->translatable()
                            ->columnSpanFull(),

                        TextInput::make('meta_title')
                            ->label('عنوان متا')
                            ->translatable(),

                        TextInput::make('meta_description')
                            ->label('توضیحات متا')
                            ->translatable(),
                    ]),

                Section::make('تنظیمات اختصاصی SQLite')
                    ->schema([
                        TextInput::make('slug_fa')->label('Slug (فارسی)'),
                        TextInput::make('slug_en')->label('Slug (انگلیسی)'),
                        TextInput::make('focus_keyword')->label('کلمه کلیدی اصلی'),
                    ])->columns(2),

                Section::make('وضعیت و ترتیب')
                    ->schema([
                        Toggle::make('is_active')->default(true),
                        Toggle::make('is_featured')->default(false),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ])->columns(3),
            ]);
    }
}