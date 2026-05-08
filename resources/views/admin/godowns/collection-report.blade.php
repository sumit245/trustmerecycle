<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Collection Report - {{ $godown->name }}</title>
    <style>
        :root {
            color-scheme: dark;
            --bg: #09090b;
            --panel: #18181b;
            --panel-soft: #202024;
            --border: #2f2f34;
            --text: #f4f4f5;
            --muted: #a1a1aa;
            --green: #22c55e;
            --green-strong: #16a34a;
            --blue: #60a5fa;
            --red: #f87171;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
        }

        .shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            border-right: 1px solid var(--border);
            background: #111113;
            padding: 20px;
        }

        .brand {
            margin: 0 0 32px;
            font-size: 17px;
            font-weight: 700;
        }

        .nav {
            display: grid;
            gap: 8px;
        }

        .nav a {
            border-radius: 8px;
            color: var(--muted);
            padding: 11px 12px;
            text-decoration: none;
        }

        .nav a.active,
        .nav a:hover {
            background: var(--panel-soft);
            color: var(--green);
        }

        main {
            padding: 32px 40px 48px;
            overflow-x: auto;
        }

        .top {
            align-items: flex-start;
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .back {
            color: var(--green);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        h1 {
            font-size: 28px;
            line-height: 1.2;
            margin: 10px 0 6px;
        }

        .subtitle {
            color: var(--muted);
            margin: 0;
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        .button {
            border-radius: 8px;
            color: white;
            display: inline-flex;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
            white-space: nowrap;
        }

        .button.excel {
            background: var(--green-strong);
        }

        .button.pdf {
            background: #3f3f46;
        }

        .stats {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            margin-bottom: 24px;
        }

        .stat {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 16px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            margin: 0 0 8px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .table-wrap {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        table {
            border-collapse: collapse;
            min-width: 1080px;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid var(--border);
            padding: 13px 14px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: var(--panel-soft);
            color: var(--muted);
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 9px;
        }

        .badge.completed {
            background: rgba(34, 197, 94, .14);
            color: var(--green);
        }

        .badge.pending {
            background: rgba(96, 165, 250, .14);
            color: var(--blue);
        }

        .files {
            display: flex;
            gap: 12px;
        }

        .files a {
            color: var(--green);
            font-weight: 700;
            text-decoration: none;
        }

        .empty {
            color: var(--muted);
            padding: 32px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-bottom: 1px solid var(--border);
                border-right: 0;
            }

            main {
                padding: 24px 18px 36px;
            }

            .top,
            .actions {
                align-items: stretch;
                flex-direction: column;
            }

            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <p class="brand">TrustmeRecycle</p>
            <nav class="nav">
                <a href="/admin">Dashboard</a>
                <a href="/admin/collection-jobs">Collection Jobs</a>
                <a class="active" href="/admin/godowns">Sites</a>
                <a href="/admin/scrap-entries">Scrap Entries</a>
                <a href="/admin/scrap-types">Scrap Types</a>
                <a href="/admin/site-incharges">Site Incharges</a>
            </nav>
        </aside>

        <main>
            <div class="top">
                <div>
                    <a href="/admin/godowns" class="back">Back to sites</a>
                    <h1>Historical Collection Report</h1>
                    <p class="subtitle">{{ $godown->name }} · {{ $godown->location }}</p>
                </div>

                <div class="actions">
                    <a href="{{ route('admin.godowns.collection-report.excel', $godown) }}" class="button excel">Export Excel</a>
                    <a href="{{ route('admin.godowns.collection-report.pdf', $godown) }}" class="button pdf">Export PDF</a>
                </div>
            </div>

            <section class="stats">
                <div class="stat">
                    <p class="stat-label">Site Incharge</p>
                    <p class="stat-value">{{ $godown->vendor?->name ?? 'Unassigned' }}</p>
                </div>
                <div class="stat">
                    <p class="stat-label">Total Jobs</p>
                    <p class="stat-value">{{ $jobs->count() }}</p>
                </div>
                <div class="stat">
                    <p class="stat-label">Completed Collections</p>
                    <p class="stat-value">{{ $completedJobsCount }}</p>
                </div>
                <div class="stat">
                    <p class="stat-label">Total Collected</p>
                    <p class="stat-value">{{ number_format($totalCollected, 2) }} MT</p>
                </div>
            </section>

            <section class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Job ID</th>
                            <th>Status</th>
                            <th>Scrap Weight</th>
                            <th>Driver</th>
                            <th>Vehicle</th>
                            <th>Dispatched At</th>
                            <th>Collected At</th>
                            <th>Files</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jobs as $job)
                            <tr>
                                <td>#{{ $job->id }}</td>
                                <td>
                                    <span class="badge {{ $job->status === 'completed' ? 'completed' : 'pending' }}">
                                        {{ str($job->status)->replace('_', ' ')->title() }}
                                    </span>
                                </td>
                                <td>{{ $job->collected_amount_mt ? number_format($job->collected_amount_mt, 2) . ' MT' : '-' }}</td>
                                <td>{{ $job->truck_details['driver_name'] ?? '-' }}</td>
                                <td>{{ $job->truck_details['vehicle_number'] ?? '-' }}</td>
                                <td>{{ $job->dispatched_at?->format('d M Y, h:i A') ?? '-' }}</td>
                                <td>{{ $job->collected_at?->format('d M Y, h:i A') ?? 'Not Picked Up' }}</td>
                                <td>
                                    <div class="files">
                                        @if($job->collection_proof_image)
                                            <a href="{{ $job->collection_proof_image_url }}" target="_blank">Scrap</a>
                                        @endif
                                        @if($job->challan_image)
                                            <a href="{{ $job->challan_image_url }}" target="_blank">Challan</a>
                                        @endif
                                        @if(!$job->collection_proof_image && !$job->challan_image)
                                            <span>-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty">No collection jobs found for this site.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
