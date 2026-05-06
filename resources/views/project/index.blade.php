@extends('layouts.app')

@section('content')
<div class="container-lg py-5">

    {{-- ══════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fas fa-folder-open me-2 text-primary"></i>Projects
            </h3>
            <p class="text-muted small mb-0">Daftar semua project maintenance yang sedang berjalan</p>
        </div>

        @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Buat Project
        </a>
        @endif
    </div>

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 small mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         EMPTY STATE
    ══════════════════════════════════════════════════════ --}}
    @if($projects->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-4 d-block opacity-25"></i>
            <h5 class="text-muted fw-semibold mb-2">Belum ada project</h5>
            <p class="text-muted small mb-4">Project yang dibuat atau diimport akan muncul di sini.</p>
            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Buat Project Pertama
                </a>
            @endif
        </div>

    {{-- ══════════════════════════════════════════════════════
         PROJECT LIST
    ══════════════════════════════════════════════════════ --}}
    @else
        <div class="row g-4">
            @foreach($projects as $project)
            @php
                $phaseConfig = [
                    'predock'  => ['label' => 'Pre Dock',  'color' => '#667eea'],
                    'indock'   => ['label' => 'In Dock',   'color' => '#f5576c'],
                    'postdock' => ['label' => 'Post Dock', 'color' => '#11998e'],
                ];
                $overallProgress = round($project->progress * 100, 1);
            @endphp

            <div class="col-12">
                <div class="card border-0 shadow-sm project-card">
                    <div class="card-body p-0">

                        {{-- ── Top bar ── --}}
                        <div class="d-flex align-items-start justify-content-between p-4 pb-3 flex-wrap gap-3">

                            {{-- Info utama --}}
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <span class="project-reg-badge">
                                        <i class="fas fa-plane me-1"></i>{{ $project->aircraft_reg }}
                                    </span>
                                    @if($project->aircraft_type)
                                        <span class="text-muted small">{{ $project->aircraft_type }}</span>
                                    @endif
                                </div>

                                <h5 class="fw-bold text-dark mb-1" style="font-size: 1rem;">
                                    {{ $project->customer }}
                                    @if($project->contract_no)
                                        <span class="fw-normal text-muted" style="font-size: 0.85rem;">
                                            — {{ $project->contract_no }}
                                        </span>
                                    @endif
                                </h5>

                                @if($project->description)
                                    <p class="text-muted small mb-0 description-clamp">
                                        {{ $project->description }}
                                    </p>
                                @endif
                            </div>

                            {{-- Overall progress --}}
                            <div class="text-end">
                                <div class="overall-progress-ring mb-1">
                                    <svg viewBox="0 0 40 40" width="56" height="56">
                                        <circle cx="20" cy="20" r="16" fill="none" stroke="#f0f0f0" stroke-width="4"/>
                                        <circle cx="20" cy="20" r="16" fill="none" stroke="#667eea" stroke-width="4"
                                                stroke-dasharray="{{ round($overallProgress * 1.005, 2) }} 100.5"
                                                stroke-linecap="round"
                                                transform="rotate(-90 20 20)"/>
                                        <text x="20" y="24" text-anchor="middle"
                                              font-size="8" font-weight="700" fill="#2c3e50">
                                            {{ $overallProgress }}%
                                        </text>
                                    </svg>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem;">Overall</div>
                            </div>

                        </div>

                        {{-- ── Dock Phase Progress bars ── --}}
                        <div class="px-4 pb-3">
                            <div class="row g-2">
                                @foreach($phaseConfig as $type => $cfg)
                                    @php
                                        $phase = $project->dockPhases->firstWhere('type', $type);
                                        $pct   = $phase ? round($phase->progress * 100, 1) : 0;
                                    @endphp
                                    <div class="col-12 col-md-4">
                                        <div class="phase-bar-block">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="phase-label" style="color: {{ $cfg['color'] }};">
                                                    {{ $cfg['label'] }}
                                                </span>
                                                <span class="phase-pct" style="color: {{ $cfg['color'] }};">
                                                    {{ $pct }}%
                                                </span>
                                            </div>
                                            <div class="progress" style="height: 5px; border-radius: 3px; background: {{ $cfg['color'] }}18;">
                                                <div class="progress-bar" role="progressbar"
                                                     style="width: {{ $pct }}%; background: {{ $cfg['color'] }};"
                                                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ── Footer ── --}}
                        <div class="card-footer bg-transparent border-top px-4 py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3 text-muted" style="font-size: 0.78rem;">
                                @if($project->start_date)
                                    <span>
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                                        —
                                        {{ $project->finish_date
                                            ? \Carbon\Carbon::parse($project->finish_date)->format('d M Y')
                                            : '?' }}
                                    </span>
                                @endif
                                @if($project->work_days)
                                    <span>
                                        <i class="fas fa-clock me-1"></i>{{ $project->work_days }} hari kerja
                                    </span>
                                @endif
                                <span>
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ $project->dock_phases_count ?? $project->dockPhases->count() }} fase
                                </span>
                            </div>

                            <a href="{{ route('projects.show', $project) }}"
                               class="btn btn-sm btn-outline-primary">
                                Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
        @endif

    @endif

</div>

<style>
    /* Project card */
    .project-card {
        border-radius: 0.75rem;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
    }

    /* Reg badge */
    .project-reg-badge {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        background: #667eea18;
        color: #667eea;
        border-radius: 2rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    /* Description clamp */
    .description-clamp {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        max-width: 600px;
    }

    /* Phase bar */
    .phase-label, .phase-pct {
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Progress ring SVG */
    .overall-progress-ring circle:nth-child(2) {
        transition: stroke-dasharray 0.6s ease;
    }
</style>
@endsection