<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Actions\ProcessProductImages;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove new_images from data before saving product
        unset($data['new_images']);
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        /** @var \App\Models\Product $record */
        $record = $this->record;
        
        $data = $this->form->getRawState();
        
        ProcessProductImages::handle($record, $data);
    }
}