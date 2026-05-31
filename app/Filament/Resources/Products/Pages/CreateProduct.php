<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Actions\ProcessProductImages;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove new_images from data before creating product
        // because it's not a real column in the products table
        unset($data['new_images']);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        /** @var \App\Models\Product $record */
        $record = $this->record;
        
        $data = $this->form->getRawState();
        
        ProcessProductImages::handle($record, $data);
    }
}