<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print MWS - {{ $mwsPart->part_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Maintenance Work Sheet</h1>
        <h2>{{ $mwsPart->title }} - {{ $mwsPart->part_number }}</h2>
    </div>
    
    <div class="info-grid">
        <div><strong>Customer:</strong> {{ $mwsPart->customer->company_name ?? '-' }}</div>
        <div><strong>Serial Number:</strong> {{ $mwsPart->serial_number }}</div>
        <div><strong>Job Type:</strong> {{ $mwsPart->job_type }}</div>
        <div><strong>Status:</strong> {{ ucfirst($mwsPart->status) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Description</th>
                <th>Plan Man</th>
                <th>Plan Hours</th>
                <th>Actual Man</th>
                <th>Actual Hours</th>
                <th>Tech</th>
                <th>Insp</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mwsPart->steps->sortBy('no') as $step)
            <tr>
                <td>{{ $step->no }}</td>
                <td>{{ $step->description }}</td>
                <td>{{ $step->plan_man ?? 'N/A' }}</td>
                <td>{{ $step->plan_hours ?? 'N/A' }}</td>
                <td>{{ count($step->man ?? []) }}</td>
                <td>{{ $step->hours ?? '00:00' }}</td>
                <td>{{ $step->tech ?? 'N/A' }}</td>
                <td>{{ $step->insp ?? 'N/A' }}</td>
                <td>{{ ucfirst($step->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>