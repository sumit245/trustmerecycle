<?php

namespace App\Http\Controllers\Admin;

use App\Exports\GodownCollectionJobsExport;
use App\Http\Controllers\Controller;
use App\Models\CollectionJob;
use App\Models\Godown;
use App\Support\SimplePdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;

class GodownCollectionReportController extends Controller
{
    public function show(Godown $godown)
    {
        $jobs = $this->jobs($godown)->get();

        return view('admin.godowns.collection-report', [
            'godown' => $godown->load('vendor'),
            'jobs' => $jobs,
            'completedJobsCount' => $jobs->where('status', 'completed')->count(),
            'totalCollected' => $jobs->where('status', 'completed')->sum('collected_amount_mt'),
        ]);
    }

    public function excel(Godown $godown)
    {
        return Excel::download(
            new GodownCollectionJobsExport($godown),
            $this->filename($godown, 'xlsx')
        );
    }

    public function pdf(Godown $godown): Response
    {
        $jobs = $this->jobs($godown)->get();
        $completedJobs = $jobs->where('status', 'completed');

        $headers = [
            str_pad('ID', 4),
            str_pad('Status', 18),
            str_pad('Weight', 12),
            str_pad('Driver', 18),
            str_pad('Vehicle', 16),
            str_pad('Dispatched', 18),
            str_pad('Collected', 18),
        ];

        $rows = $jobs->map(function (CollectionJob $job): array {
            return [
                str_pad((string) $job->id, 4),
                str_pad($this->formatStatus($job->status), 18),
                str_pad(($job->collected_amount_mt ?? '-') . ' MT', 12),
                str_pad($this->limit($job->truck_details['driver_name'] ?? '-', 18), 18),
                str_pad($this->limit($job->truck_details['vehicle_number'] ?? '-', 16), 16),
                str_pad($job->dispatched_at?->format('Y-m-d H:i') ?? '-', 18),
                str_pad($job->collected_at?->format('Y-m-d H:i') ?? 'Not Picked Up', 18),
            ];
        })->all();

        $pdf = SimplePdf::tableReport(
            'Historical Collection Report - ' . $godown->name,
            [
                'Site' => $godown->name,
                'Location' => $godown->location,
                'Site Incharge' => $godown->vendor?->name ?? 'Unassigned',
                'Total Jobs' => (string) $jobs->count(),
                'Completed Collections' => (string) $completedJobs->count(),
                'Total Collected' => number_format((float) $completedJobs->sum('collected_amount_mt'), 2) . ' MT',
                'Generated At' => now()->format('Y-m-d H:i:s'),
            ],
            $headers,
            $rows
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($godown, 'pdf') . '"',
        ]);
    }

    private function jobs(Godown $godown)
    {
        return $godown
            ->collectionJobs()
            ->latest('dispatched_at')
            ->latest('created_at');
    }

    private function filename(Godown $godown, string $extension): string
    {
        $site = str($godown->name)->slug('-');

        return "collection-history-{$site}-" . now()->format('Y-m-d') . ".{$extension}";
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

    private function limit(string $value, int $length): string
    {
        return strlen($value) > $length ? substr($value, 0, $length - 1) . '.' : $value;
    }
}
