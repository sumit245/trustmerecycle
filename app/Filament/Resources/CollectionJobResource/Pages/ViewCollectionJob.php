<?php

namespace App\Filament\Resources\CollectionJobResource\Pages;

use App\Filament\Resources\CollectionJobResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewCollectionJob extends ViewRecord
{
    protected static string $resource = CollectionJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('godown_id')
                    ->label('Site')
                    ->relationship('godown', 'name')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'truck_dispatched' => 'Truck Dispatched',
                        'completed' => 'Completed',
                    ])
                    ->disabled(),
                Forms\Components\KeyValue::make('truck_details')
                    ->label('Truck Details')
                    ->keyLabel('Field')
                    ->valueLabel('Value')
                    ->disabled(),
                Forms\Components\TextInput::make('collected_amount_mt')
                    ->label('Scrap weight')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Placeholder::make('collection_proof_image')
                    ->label('Scrap Image')
                    ->content(function ($record) {
                        if (!$record->collection_proof_image) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic">No scrap image uploaded</p>');
                        }
                        
                        $imageUrl = Storage::disk('public')->url($record->collection_proof_image);
                        $fileName = basename($record->collection_proof_image);
                        
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-2">' .
                            '<img src="' . $imageUrl . '" alt="Collection Scrap Image" class="max-w-full h-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 400px;" />' .
                            '<p class="text-sm text-gray-500">' . $fileName . '</p>' .
                            '</div>'
                        );
                    }),
                Forms\Components\Placeholder::make('challan_image')
                    ->label('Challan')
                    ->content(function ($record) {
                        if (!$record->challan_image) {
                            return new \Illuminate\Support\HtmlString('<p class="text-sm text-gray-500 italic">No challan image uploaded</p>');
                        }
                        
                        $imageUrl = Storage::disk('public')->url($record->challan_image);
                        $fileName = basename($record->challan_image);
                        
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-2">' .
                            '<img src="' . $imageUrl . '" alt="Challan Image" class="max-w-full h-auto rounded-lg border border-gray-300 shadow-sm" style="max-height: 400px;" />' .
                            '<p class="text-sm text-gray-500">' . $fileName . '</p>' .
                            '</div>'
                        );
                    }),
                Forms\Components\DateTimePicker::make('dispatched_at')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('collected_at')
                    ->disabled(),
            ]);
    }
}

