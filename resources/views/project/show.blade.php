@extends('layouts.app')

@section('content')
    <div class="container-lg py-5">

        {{-- ══════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════ --}}
        <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('projects.index') }}" class="text-muted text-decoration-none small">
                        <i class="fas fa-folder me-1"></i>Projects
                    </a>
                    <span class="text-muted small">/</span>
                    <span class="small text-dark fw-semibold">{{ $project->aircraft_reg }}</span>
                </div>
                <h3 class="fw-bold text-dark mb-1">
                    <i class="fas fa-plane me-2 text-primary"></i>
                    {{ $project->aircraft_type }} — {{ $project->aircraft_reg }}
                </h3>
                <p class="text-muted small mb-0">{{ $project->customer }}
                    @if ($project->contract_no)
                        &nbsp;·&nbsp; {{ $project->contract_no }}
                    @endif
                    @if ($project->start_date)
                        &nbsp;·&nbsp;
                        {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                        —
                        {{ $project->finish_date ? \Carbon\Carbon::parse($project->finish_date)->format('d M Y') : '?' }}
                    @endif
                </p>
            </div>

            @can('is-management')
                <div class="d-flex gap-2">
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                        onsubmit="return confirm('Hapus project ini? Semua data terkait akan ikut terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            @endcan
        </div>

        {{-- Flash message --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 small mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════
         SUMMARY CARDS (3 dock phases)
    ══════════════════════════════════════════════════════ --}}
        <div class="row g-3 mb-4">
            @php
                $phaseConfig = [
                    'predock' => [
                        'label' => 'Pre Dock',
                        'icon' => 'fas fa-sign-in-alt',
                        'color' => '#667eea',
                        'bg' => '#f0f0ff',
                    ],
                    'indock' => [
                        'label' => 'In Dock',
                        'icon' => 'fas fa-tools',
                        'color' => '#f5576c',
                        'bg' => '#fff0f2',
                    ],
                    'postdock' => [
                        'label' => 'Post Dock',
                        'icon' => 'fas fa-sign-out-alt',
                        'color' => '#11998e',
                        'bg' => '#f0fdf9',
                    ],
                ];
            @endphp

            @foreach ($phaseConfig as $type => $cfg)
                @php
                    $phase = $project->dockPhases->firstWhere('type', $type);
                    $progress = $phase ? round($phase->progress * 100, 1) : 0;
                @endphp
                <div class="col-12 col-md-4">
                    <div class="card border-0 shadow-sm h-100 summary-card"
                        style="cursor: pointer; border-left: 4px solid {{ $cfg['color'] }} !important;"
                        onclick="switchToTab('tab-{{ $type }}')">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="phase-icon"
                                        style="background: {{ $cfg['bg'] }}; color: {{ $cfg['color'] }};">
                                        <i class="{{ $cfg['icon'] }}"></i>
                                    </span>
                                    <span class="fw-semibold text-dark"
                                        style="font-size: 0.9rem;">{{ $cfg['label'] }}</span>
                                </div>
                                <span class="fw-bold" style="color: {{ $cfg['color'] }}; font-size: 0.95rem;">
                                    {{ $progress }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $progress }}%; background: {{ $cfg['color'] }};"
                                    aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            @if ($phase)
                                <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                                    @if ($phase->start_date)
                                        {{ \Carbon\Carbon::parse($phase->start_date)->format('d M Y') }}
                                        —
                                        {{ $phase->finish_date ? \Carbon\Carbon::parse($phase->finish_date)->format('d M Y') : '?' }}
                                    @else
                                        <span class="fst-italic">Tanggal belum diset</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════
         TABS
    ══════════════════════════════════════════════════════ --}}
        <ul class="nav nav-tabs mb-0" id="dockTabs">
            @foreach ($phaseConfig as $type => $cfg)
                @php $phase = $project->dockPhases->firstWhere('type', $type); @endphp
                <li class="nav-item">
                    <button class="nav-link dock-tab {{ $loop->first ? 'active' : '' }}" id="tab-{{ $type }}"
                        data-type="{{ $type }}" onclick="switchToTab('tab-{{ $type }}')"
                        style="--tab-color: {{ $cfg['color'] }};">
                        <i class="{{ $cfg['icon'] }} me-1"></i>{{ $cfg['label'] }}
                        @if ($phase)
                            <span class="ms-2 badge rounded-pill"
                                style="background: {{ $cfg['color'] }}20; color: {{ $cfg['color'] }}; font-size: 0.7rem;">
                                {{ $phase->taskGroups->count() }} grup
                            </span>
                        @endif
                    </button>
                </li>
            @endforeach
        </ul>

        {{-- ══════════════════════════════════════════════════════
         TAB CONTENT
    ══════════════════════════════════════════════════════ --}}
        <div class="tab-content-wrapper border border-top-0 rounded-bottom shadow-sm bg-white">
            @foreach ($phaseConfig as $type => $cfg)
                @php $phase = $project->dockPhases->firstWhere('type', $type); @endphp

                <div class="dock-panel {{ $loop->first ? '' : 'd-none' }}" id="panel-{{ $type }}">

                    @if (!$phase || $phase->taskGroups->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                            <p class="mb-0">Belum ada task group di fase ini.</p>
                        </div>
                    @else
                        <div class="p-3 p-md-4">
                            @foreach ($phase->taskGroups->sortBy('no') as $taskGroup)
                                {{-- ── Task Group (Level 3) ── --}}
                                <div class="task-group-block mb-3">
                                    <div class="task-group-header d-flex align-items-center justify-content-between px-3 py-2 rounded-top"
                                        style="background: {{ $cfg['color'] }}12; border-left: 3px solid {{ $cfg['color'] }};">
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-link btn-sm p-0 text-muted toggle-btn"
                                                onclick="toggleGroup(this)" title="Expand/Collapse">
                                                <i class="fas fa-chevron-down toggle-icon"
                                                    style="font-size: 0.75rem; transition: transform 0.2s;"></i>
                                            </button>
                                            @if ($taskGroup->no)
                                                <span class="badge fw-semibold"
                                                    style="background: {{ $cfg['color'] }}; font-size: 0.72rem;">
                                                    {{ $taskGroup->no }}
                                                </span>
                                            @endif
                                            <span class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                                {{ $taskGroup->name }}
                                            </span>
                                        </div>
                                        <span class="text-muted small">
                                            {{ $taskGroup->tasks->count() }} task
                                        </span>
                                    </div>

                                    {{-- ── Tasks (Level 4) ── --}}
                                    <div class="task-group-body border border-top-0 rounded-bottom">
                                        @forelse($taskGroup->tasks->sortBy('no') as $task)
                                            <div class="task-row px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            @if ($task->no)
                                                                <span class="text-muted small fw-semibold"
                                                                    style="min-width: 2rem;">{{ $task->no }}</span>
                                                            @endif
                                                            <span class="text-dark"
                                                                style="font-size: 0.875rem;">{{ $task->name }}</span>
                                                        </div>

                                                        {{-- MWS Parts (Level 5) --}}
                                                        @if ($task->mwsParts->isNotEmpty())
                                                            <div
                                                                class="d-flex flex-wrap gap-2 mt-1 ms-{{ $task->no ? '4' : '0' }}">
                                                                @foreach ($task->mwsParts as $mws)
                                                                    <a href="{{ route('mws.show', $mws->id) }}"
                                                                        class="mws-chip" title="{{ $mws->name }}">
                                                                        <i class="fas fa-file-alt me-1"
                                                                            style="font-size: 0.65rem;"></i>
                                                                        {{ $mws->part_id ?? $mws->name }}
                                                                        <span
                                                                            class="mws-status mws-status--{{ $mws->status ?? 'open' }}">
                                                                            {{ ucfirst($mws->status ?? 'open') }}
                                                                        </span>
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Progress task --}}
                                                    @php $taskProgress = round(($task->progress ?? 0) * 100, 0); @endphp
                                                    <div class="text-end" style="min-width: 50px;">
                                                        <span class="small fw-semibold"
                                                            style="color: {{ $cfg['color'] }};">
                                                            {{ $taskProgress }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="px-3 py-2 text-muted small fst-italic">
                                                Belum ada task.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════
     STYLES
══════════════════════════════════════════════════════ --}}
    <style>
        /* Summary cards */
        .summary-card {
            border-left-width: 4px !important;
            transition: transform 0.15s, box-shadow 0.15s;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .phase-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        /* Tabs */
        .nav-tabs {
            border-bottom: none;
            gap: 4px;
        }

        .nav-tabs .dock-tab {
            border: 1px solid #dee2e6;
            border-bottom: none;
            border-radius: 0.5rem 0.5rem 0 0;
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.5rem 1.1rem;
            background: #f8f9fa;
            transition: all 0.15s;
        }

        .nav-tabs .dock-tab:hover {
            background: #fff;
            color: var(--tab-color);
        }

        .nav-tabs .dock-tab.active {
            background: #fff;
            color: var(--tab-color);
            border-top: 2px solid var(--tab-color);
            font-weight: 600;
        }

        /* Tab content wrapper */
        .tab-content-wrapper {
            border-color: #dee2e6 !important;
        }

        /* Task group */
        .task-group-header {
            cursor: default;
        }

        .task-group-body {
            border-color: #e9ecef !important;
        }

        /* Task row */
        .task-row {
            transition: background 0.1s;
        }

        .task-row:hover {
            background: #f8f9fa;
        }

        /* MWS chip */
        .mws-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 10px;
            border-radius: 2rem;
            background: #f1f3f5;
            border: 1px solid #dee2e6;
            font-size: 0.76rem;
            color: #495057;
            text-decoration: none;
            transition: all 0.15s;
        }

        .mws-chip:hover {
            background: #e9ecef;
            color: #212529;
            border-color: #adb5bd;
            text-decoration: none;
        }

        /* MWS status badge */
        .mws-status {
            font-size: 0.65rem;
            padding: 1px 6px;
            border-radius: 2rem;
            font-weight: 600;
            margin-left: 2px;
        }

        .mws-status--open {
            background: #e3f2fd;
            color: #1565c0;
        }

        .mws-status--inprogress {
            background: #fff8e1;
            color: #e65100;
        }

        .mws-status--done {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .mws-status--closed {
            background: #f3e5f5;
            color: #6a1b9a;
        }
    </style>

    {{-- ══════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════ --}}
    <script>
        function switchToTab(tabId) {
            // Update tab buttons
            document.querySelectorAll('.dock-tab').forEach(btn => {
                btn.classList.toggle('active', btn.id === tabId);
            });

            // Update panels
            const type = tabId.replace('tab-', '');
            document.querySelectorAll('.dock-panel').forEach(panel => {
                panel.classList.toggle('d-none', panel.id !== 'panel-' + type);
            });
        }

        function toggleGroup(btn) {
            const body = btn.closest('.task-group-block').querySelector('.task-group-body');
            const icon = btn.querySelector('.toggle-icon');
            const isHidden = body.classList.toggle('d-none');
            icon.style.transform = isHidden ? 'rotate(-90deg)' : 'rotate(0deg)';
        }
    </script>
@endsection
