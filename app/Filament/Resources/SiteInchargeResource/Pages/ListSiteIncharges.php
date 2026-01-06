<?php

namespace App\Filament\Resources\SiteInchargeResource\Pages;

use App\Filament\Resources\SiteInchargeResource;
use App\Exports\SiteInchargeExport;
use App\Imports\VendorsImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ListSiteIncharges extends ListRecords
{
    protected static string $resource = SiteInchargeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Import Site Incharges')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Site Incharges from Excel')
                ->modalDescription('Upload an Excel file (.xlsx, .xls, or .csv) with Site Incharge data. The first row should contain column headers.')
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
                ])
                ->action(function (array $data) {
                    $filePath = $data['file'];
                    
                    try {
                        $import = new VendorsImport();
                        
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
                        $message .= $successCount > 0 ? "Successfully imported {$successCount} Site Incharge(s). " : "";
                        $message .= $skippedCount > 0 ? "Skipped {$skippedCount} row(s) (already exist). " : "";
                        
                        // Log errors if any
                        if (!empty($errors)) {
                            \Log::warning('Site Incharge import completed with errors', [
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
                        \Log::error('Site Incharge import failed: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Import Failed')
                            ->body('An error occurred during import: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('export')
                ->label('Export Site Incharges')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(function () {
                    return Excel::download(new SiteInchargeExport(), 'site-incharges-' . now()->format('Y-m-d') . '.xlsx');
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            //
        ];
    }
}

