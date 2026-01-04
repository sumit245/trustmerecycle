<?php

namespace App\Filament\Resources\GodownResource\Pages;

use App\Filament\Resources\GodownResource;
use App\Imports\GodownsImport;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListGodowns extends ListRecords
{
    protected static string $resource = GodownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Godowns')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Godowns from Excel')
                ->modalDescription('Upload an Excel file (.xlsx, .xls, or .csv) with godown data. The first row should contain column headers. If vendor or location columns are missing, defaults will be used.')
                ->modalSubmitActionLabel('Import')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Excel File')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                        ->maxSize(10240)
                        ->disk('local')
                        ->directory('imports')
                        ->visibility('private')
                        ->helperText('Supported formats: .xlsx, .xls, .csv. Maximum file size: 10MB'),
                    \Filament\Forms\Components\Select::make('default_vendor_id')
                        ->label('Default Vendor (if not in Excel)')
                        ->options(function () {
                            return User::where('role', 'vendor')->pluck('name', 'id')->toArray();
                        })
                        ->searchable()
                        ->preload()
                        ->helperText('If vendor column is missing in Excel, all godowns will be assigned to this vendor'),
                    \Filament\Forms\Components\TextInput::make('default_location')
                        ->label('Default Location (if not in Excel)')
                        ->default('Noida')
                        ->maxLength(255)
                        ->helperText('If location column is missing in Excel, this value will be used for all godowns'),
                ])
                ->action(function (array $data) {
                    $filePath = $data['file'];
                    $defaultVendorId = $data['default_vendor_id'] ?? null;
                    $defaultLocation = $data['default_location'] ?? 'Noida';
                    
                    try {
                        $import = new GodownsImport($defaultVendorId, $defaultLocation);
                        
                        // Get the full path to the stored file
                        $fullPath = Storage::disk('local')->path($filePath);
                        
                        // Import the file
                        Excel::import($import, $fullPath);
                        
                        // Get results
                        $successCount = $import->getSuccessCount();
                        $skippedCount = $import->getSkippedCount();
                        $errors = $import->getErrors();
                        
                        // Build notification message
                        $message = "Import completed. ";
                        $message .= $successCount > 0 ? "Successfully imported {$successCount} godown(s). " : "";
                        $message .= $skippedCount > 0 ? "Skipped {$skippedCount} row(s). " : "";
                        
                        // Log errors if any
                        if (!empty($errors)) {
                            \Log::warning('Godowns import completed with errors', [
                                'success_count' => $successCount,
                                'skipped_count' => $skippedCount,
                                'errors' => $errors,
                            ]);
                            $message .= "Some errors occurred. Check the logs for details.";
                        }
                        
                        // Show notification based on results
                        if ($successCount > 0 && empty($errors)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Successful')
                                ->body($message)
                                ->success()
                                ->send();
                        } elseif ($successCount > 0 && !empty($errors)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Completed with Warnings')
                                ->body($message)
                                ->warning()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Import Failed')
                                ->body($message)
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Godowns import failed: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Import Failed')
                            ->body('An error occurred during import: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}

