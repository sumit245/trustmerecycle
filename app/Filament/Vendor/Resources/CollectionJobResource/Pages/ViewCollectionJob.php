<?php

namespace App\Filament\Vendor\Resources\CollectionJobResource\Pages;

use App\Filament\Vendor\Resources\CollectionJobResource;
use App\Models\CollectionJob;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewCollectionJob extends ViewRecord
{
    protected static string $resource = CollectionJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('complete')
                ->label('Complete Job')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\FileUpload::make('collection_proof_image')
                        ->label('Collection Proof Image')
                        ->image()
                        ->directory('proofs')
                        ->disk('public')
                        ->visibility('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                        ->maxSize(5120)
                        ->required()
                        ->helperText('Upload photo of empty godown or truck loading (Max 5MB, JPG/PNG)'),
                    Forms\Components\TextInput::make('collected_amount_mt')
                        ->label('Collected Amount (MT)')
                        ->numeric()
                        ->required()
                        ->step(0.01)
                        ->minValue(0.01)
                        ->maxValue(function () {
                            $user = Auth::user();
                            $godown = $user->godowns()->first();
                            return $godown ? $godown->current_stock_mt : 0;
                        })
                        ->helperText(function () {
                            $user = Auth::user();
                            $godown = $user->godowns()->first();
                            $max = $godown ? $godown->current_stock_mt : 0;
                            return "Maximum: " . number_format($max, 2) . " MT";
                        }),
                ])
                ->action(function (array $data) {
                    $record = $this->record;
                    $user = Auth::user();
                    $godown = $user->godowns()->first();

                    // Validate ownership
                    if (!$godown || $record->godown_id !== $godown->id) {
                        throw new \Exception('Unauthorized access.');
                    }

                    // Validate status
                    if (!$record->isDispatched()) {
                        throw new \Exception('This job is not in dispatched status.');
                    }

                    // Validate collected amount
                    if ($data['collected_amount_mt'] > $godown->current_stock_mt) {
                        throw new \Exception('Collected amount cannot exceed current stock.');
                    }

                    // Get the file path from the uploaded file
                    $imagePath = $data['collection_proof_image'];

                    // Mark job as completed and reduce stock
                    $record->markCompleted($data['collected_amount_mt'], $imagePath);
                    $godown->reduceStock($data['collected_amount_mt']);
                })
                ->visible(fn () => $this->record->isDispatched())
                ->successNotificationTitle('Collection job completed successfully!'),
        ];
    }
}

