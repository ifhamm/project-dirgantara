@extends('layouts.app')

@section('content')
<div class="container-lg py-5">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark">
                        <i class="fas fa-list-check me-2 text-primary"></i>Tracking List MWS
                    </h3>
                    <p class="text-muted small mt-1">Daftar semua Maintenance Work Sheet yang telah dibuat</p>
                </div>
                <a href="{{ route('mws.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create MWS
                </a>
            </div>
        </div>
    </div>

    {{-- Filter & Search (Optional) --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <input type="text" class="form-control" placeholder="Cari berdasarkan Part Name..." />
                </div>
                <div class="col-12 col-md-4">
                    <select class="form-select">
                        <option value="">-- Filter Status --</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <button class="btn btn-outline-secondary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MWS List Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0">
                <i class="fas fa-database me-2 text-secondary"></i>
                Daftar MWS ({{ $mwsParts->total() }} items)
            </h5>
        </div>
        <div class="card-body p-0">
            @if ($mwsParts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Part ID</th>
                                <th class="px-4 py-3">Part Name</th>
                                <th class="px-4 py-3">Part Number</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Job Type</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-center">Progress</th>
                                <th class="px-4 py-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mwsParts as $mws)
                                @php
                                    $statusClass = match($mws->status) {
                                        'completed' => 'bg-info',
                                        'in_progress' => 'bg-success',
                                        default => 'bg-warning'
                                    };
                                    $statusLabel = match($mws->status) {
                                        'completed' => 'Completed',
                                        'in_progress' => 'In Progress',
                                        default => 'Pending'
                                    };
                                @endphp
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <span class="badge bg-secondary">{{ $mws->part_id }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="fw-semibold text-dark">{{ $mws->title }}</div>
                                        <small class="text-muted">{{ $mws->serial_number }}</small>
                                    </td>
                                    <td class="px-4 py-3">
                                        <code>{{ $mws->part_number }}</code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-dark">{{ $mws->customer_name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark">{{ $mws->job_type }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $totalSteps = $mws->steps->count();
                                            $completedSteps = $mws->steps->where('status', 'completed')->count();
                                            $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <small class="text-muted" style="min-width: 30px;">{{ $progress }}%</small>
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar" role="progressbar" 
                                                    style="width: {{ $progress }}%" 
                                                    aria-valuenow="{{ $progress }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="{{ route('mws.show', $mws->id) }}" 
                                            class="btn btn-sm btn-outline-primary" 
                                            title="View Details">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-end p-4 border-top">
                    {{ $mwsParts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                    <p class="text-muted mb-3">Belum ada MWS yang dibuat.</p>
                    <a href="{{ route('mws.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Buat MWS Pertama Anda
                    </a>
                </div>
            @endif
        </div>
    </div>

</div>

<style>
    .progress-bar {
        transition: width 0.6s ease;
    }
    
    table tbody tr {
        transition: background-color 0.2s ease;
    }
    
    table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9rem;
        }

        .px-4 {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }
    }
</style>

@endsection
