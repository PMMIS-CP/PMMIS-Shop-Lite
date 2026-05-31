<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                        TextInput::make('sku')
                            ->label('SKU')
                            ->disabled() 
                            ->dehydrated(false)
                            ->placeholder('به صورت خودکار ساخته می‌شود'),
                        TextInput::make('price_usd')->required()->numeric()->prefix('$'),
                        TextInput::make('stock')->required()->numeric()->default(0),
                        TextInput::make('weight')->numeric(),
                    ])->columns(3),

                Section::make('تصاویر محصول')
                    ->schema([
                        Repeater::make('new_images')
                            ->label('آپلود تصاویر جدید')
                            ->dehydrated(false)
                            // بدون relationship چون پردازش دستی با ImageUploadService انجام میشه
                            ->schema([
                                FileUpload::make('image')
                                    ->label('تصویر')
                                    ->image()
                                    // ->disk('livewire-tmp')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->imagePreviewHeight('250')
                                    ->panelAspectRatio('1:1')
                                    ->panelLayout('integrated')
                                    ->columnSpanFull(),
                                    
                                TextInput::make('alt_text')
                                    ->label('متن جایگزین (Alt)')
                                    ->maxLength(255),
                                    
                                Toggle::make('is_featured')
                                    ->label('تصویر شاخص')
                                    ->default(false),
                                    
                                TextInput::make('sort_order')
                                    ->label('ترتیب نمایش')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('افزودن تصویر')
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('محتوای چندزبانه')
                    ->schema([
                        Tabs::make('Languages')
                            ->tabs([
                                Tab::make('فارسی')
                                    ->schema([
                                        TextInput::make('name.fa')->label('نام محصول (فارسی)')->required(),
                                        TextInput::make('slug.fa')->label('نامک (فارسی)')->required(),
                                        Textarea::make('short_description.fa')->label('توضیحات کوتاه (فارسی)'),
                                        Textarea::make('description.fa')->label('توضیحات کامل (فارسی)'),
                                        TextInput::make('meta_title.fa')->label('عنوان متا (فارسی)'),
                                        TextInput::make('meta_description.fa')->label('توضیحات متا (فارسی)'),
                                    ]),
                                Tab::make('English')
                                    ->schema([
                                        TextInput::make('name.en')->label('Product Name (English)')->required(),
                                        TextInput::make('slug.en')->label('Slug (English)')->required(),
                                        Textarea::make('short_description.en')->label('Short Description (English)'),
                                        Textarea::make('description.en')->label('Description (English)'),
                                        TextInput::make('meta_title.en')->label('Meta Title (English)'),
                                        TextInput::make('meta_description.en')->label('Meta Description (English)'),
                                    ]),
                            ])->columnSpanFull(),
                    ]),

                Section::make('وضعیت و تنظیمات')
                    ->schema([
                        Toggle::make('is_active')->default(true),
                        Toggle::make('is_featured')->default(false),
                        TextInput::make('sort_order')->numeric()->default(0),
                        TextInput::make('focus_keyword')->label('کلمه کلیدی اصلی'),
                    ])->columns(3),
            ]);
    }
}