<?php

namespace App\Exports;

use App\Models\CollectionJob;
use App\Models\Godown;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GodownCollectionJobsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        private readonly Godown $godown
    ) {
    }

    public function collection()
    {
        return $this->godown
            ->collectionJobs()
            ->latest('dispatched_at')
            ->latest('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Job ID',
            'Site',
            'Status',
            'Scrap Weight (MT)',
            'Driver Name',
            'Vehicle Number',
            'Dispatched At',
            'Collected At',
            'Scrap Image',
            'Challan Image',
        ];
    }

    public function map($job): array
    {
        /** @var CollectionJob $job */
        return [
            $job->id,
            $this->godown->name,
            $this->formatStatus($job->status),
            $job->collected_amount_mt,
            $job->truck_details['driver_name'] ?? '',
            $job->truck_details['vehicle_number'] ?? '',
            $job->dispatched_at?->format('Y-m-d H:i:s') ?? '',
            $job->collected_at?->format('Y-m-d H:i:s') ?? 'Not Picked Up',
            $job->collection_proof_image ? $job->collection_proof_image_url : '',
            $job->challan_image ? $job->challan_image_url : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Collection History';
    }

    private function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pending',
            'truck_dispatched' => 'Truck Dispatched',
            'completed' => 'Completed',
            default => $status,
        };
    }
}
