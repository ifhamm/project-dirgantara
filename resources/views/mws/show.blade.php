@php
    if (!isset($isMwsLocked)) {
        $isMwsLocked = !($mwsPart->prepared_by && $mwsPart->approved_by);
    }
@endphp
@extends('layouts.app')

@section('title', 'MWS ' . $mwsPart->part_number . ' - Sistem Aircraft Maintenance')

@push('styles')
    <style>
        /* Animations */
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
        @keyframes subtle-pulse { 0%, 100% { background-color: #f8d7da; } 50% { background-color: #f5c2c7; } }

        /* Notifications */
        #stripping-notification { position: fixed; top: 5rem; right: 1rem; padding: 1rem; border-radius: 0.5rem; color: white; z-index: 50; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1); min-width: 300px; max-width: 400px; }
        #stripping-notification.warning { background-color: #ffc107; }
        #stripping-notification.critical { background-color: #dc3545; animation: pulse 2s infinite; }
        #stripping-notification.safe { background-color: #28a745; }

        #toast-notification { position: fixed; top: 1rem; right: 1rem; padding: 1rem; border-radius: 0.5rem; color: white; z-index: 1050; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1); min-width: 320px; max-width: 400px; animation: slideIn .3s ease-out; display: none; }
        #toast-notification.success { background-color: #28a745; }
        #toast-notification.error { background-color: #dc3545; }
        #toast-notification.info { background-color: #0dcaf0; }

        /* Stripping styles */
        .stripping-progress-bar { width: 100%; height: 8px; background: rgba(255, 255, 255, .3); border-radius: 4px; margin: .5rem 0; overflow: hidden; }
        .stripping-progress-fill { height: 100%; background: white; border-radius: 4px; transition: width .3s ease; }
        .stripping-warning { background-color: #fff3cd !important; border-left: 4px solid #ffc107; }
        .stripping-critical { background-color: #f8d7da !important; border-left: 4px solid #dc3545; animation: subtle-pulse 3s infinite; }
    </style>
@endpush

@section('content')
    @php
        $mwsConfig = json_encode([
            'partId' => $mwsPart->id,
            'csrfToken' => csrf_token(),
            'userRole' => auth()->user()->getRoleNames()->first() ?? '',
            'userNik' => auth()->user()->nik ?? '',
            'isLocked' => $isMwsLocked,
        ]);
    @endphp
    <div id="mws-app" data-mws='{{ $mwsConfig }}' class="min-vh-100 bg-light">

        {{-- ==================== STRIPPING NOTIFICATION ==================== --}}
        <div id="stripping-notification" style="display:none;">
            <div class="d-flex align-items-start gap-2">
                <div><i id="stripping-icon" class="fas fa-exclamation-triangle"></i></div>
                <div class="flex-grow-1">
                    <h4 class="fw-bold mb-1">Peringatan Stripping</h4>
                    <p id="stripping-message" class="mb-2"></p>
                    <div class="stripping-progress-bar">
                        <div id="stripping-progress-fill" class="stripping-progress-fill" style="width:100%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <span id="stripping-percentage">100%</span>
                        <span id="stripping-deadline"></span>
                    </div>
                </div>
                <button onclick="dismissStrippingNotification()" class="btn-close btn-close-white"></button>
            </div>
        </div>

        {{-- ==================== TOAST ==================== --}}
        <div id="toast-notification">
            <div class="d-flex align-items-start justify-content-between">
                <div class="d-flex align-items-start gap-2">
                    <i id="toast-icon" class="fas fa-check-circle"></i>
                    <span id="toast-message" class="small fw-medium"></span>
                </div>
                <button onclick="dismissToast()" class="btn-close btn-close-white ms-3"></button>
            </div>
        </div>

        {{-- ==================== TOP HEADER ==================== --}}
        <nav class="navbar navbar-light bg-white border-bottom sticky-top">
            <div class="container-fluid">
                <div class="d-flex align-items-center w-100">
                    <a href="{{ route('mws.tracking') }}" class="btn btn-light btn-sm me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div>
                        <h4 class="mb-0">Customer: {{ $mwsPart->customer_name ?? '-' }}</h4>
                        <small class="text-muted">Serial Number: {{ $mwsPart->serial_number }}</small>
                    </div>
                    <div class="ms-auto">
                        @php
                            $statusClass = match ($mwsPart->status) {
                                'completed' => 'bg-info',
                                'in_progress' => 'bg-success',
                                default => 'bg-danger',
                            };
                            $statusLabel = match ($mwsPart->status) {
                                'completed' => 'Completed',
                                'in_progress' => 'In Progress',
                                default => 'Pending',
                            };
                        @endphp
                        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">

            {{-- ==================== INFORMASI MWS ==================== --}}
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi MWS</h5>
                    <div class="d-flex gap-2">
                        @can('update', $mwsPart)
                            <button onclick="toggleEditMwsInfo(true)" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <button onclick="confirmDuplicateMws('{{ $mwsPart->id }}')" class="btn btn-sm btn-outline-purple">
                                <i class="fas fa-copy me-1"></i>Duplicate
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    {{-- VIEW MODE --}}
                    <div id="mws-info-view" class="row">
                        @php
                            $infoFields = [
                                ['label' => 'Tittle / Part Name', 'value' => $mwsPart->title],
                                ['label' => 'Part Number', 'value' => $mwsPart->part_number],
                                ['label' => 'Ref', 'value' => $mwsPart->ref ?? 'N/A'],
                                ['label' => 'Component Order', 'value' => $mwsPart->job_type ?? 'N/A'],
                                ['label' => 'Customer', 'value' => $mwsPart->customer_name ?? '-'],
                                ['label' => 'A/C Type', 'value' => $mwsPart->acType ?? 'N/A'],
                                ['label' => 'Serial Number', 'value' => $mwsPart->serial_number],
                                ['label' => 'WBS No.', 'value' => $mwsPart->wbsNO ?? 'N/A'],
                                ['label' => 'Worksheet No.', 'value' => $mwsPart->wroksheetNo ?? 'N/A'],
                                ['label' => 'IWO No.', 'value' => $mwsPart->iwo_no],
                                ['label' => 'Shop Area', 'value' => $mwsPart->shopArea ?? 'N/A'],
                                ['label' => 'Revision', 'value' => $mwsPart->revision ?? 'N/A'],
                                ['label' => 'Zone', 'value' => $mwsPart->zone ?? 'N/A'],
                                [
                                    'label' => 'Start Date',
                                    'value' => $mwsPart->start_date
                                        ? \Carbon\Carbon::parse($mwsPart->start_date)->format('d/m/Y')
                                        : 'N/A',
                                ],
                                ['label' => 'Status', 'value' => ucfirst($mwsPart->status)],
                            ];
                        @endphp
                        @foreach ($infoFields as $field)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                <div class="bg-light p-3 rounded-2 h-100 border">
                                    <small class="d-block text-muted text-uppercase fw-bold mb-2">{{ $field['label'] }}</small>
                                    <p class="mb-0 fw-medium">{{ $field['value'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- EDIT MODE --}}
                    <form id="mws-info-edit" class="d-none" onsubmit="saveMwsInfo(event)">
                        @csrf @method('PUT')
                        <div class="row">
                            @php
                                $editFields = [
                                    ['name' => 'title', 'label' => 'Tittle / Part Name', 'value' => $mwsPart->title],
                                    ['name' => 'part_number', 'label' => 'Part Number', 'value' => $mwsPart->part_number],
                                    ['name' => 'ref', 'label' => 'Ref', 'value' => $mwsPart->ref],
                                    ['name' => 'job_type', 'label' => 'Component Order', 'value' => $mwsPart->job_type],
                                    ['name' => 'acType', 'label' => 'A/C Type', 'value' => $mwsPart->acType],
                                    ['name' => 'serial_number', 'label' => 'Serial Number', 'value' => $mwsPart->serial_number],
                                    ['name' => 'wbsNO', 'label' => 'WBS No.', 'value' => $mwsPart->wbsNO],
                                    ['name' => 'wroksheetNo', 'label' => 'Worksheet No.', 'value' => $mwsPart->wroksheetNo],
                                    ['name' => 'iwo_no', 'label' => 'IWO No.', 'value' => $mwsPart->iwo_no],
                                    ['name' => 'shopArea', 'label' => 'Shop Area', 'value' => $mwsPart->shopArea],
                                    ['name' => 'revision', 'label' => 'Revision', 'value' => $mwsPart->revision],
                                    ['name' => 'zone', 'label' => 'Zone', 'value' => $mwsPart->zone],
                                    ['name' => 'start_date', 'label' => 'Start Date', 'value' => $mwsPart->start_date, 'type' => 'date'],
                                ];
                            @endphp
                            @foreach ($editFields as $f)
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                    <label class="form-label small fw-bold">{{ $f['label'] }}</label>
                                    <input type="{{ $f['type'] ?? 'text' }}" name="{{ $f['name'] }}" value="{{ $f['value'] }}" class="form-control form-control-sm">
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save me-1"></i>Simpan</button>
                            <button type="button" onclick="toggleEditMwsInfo(false)" class="btn btn-sm btn-secondary"><i class="fas fa-times me-1"></i>Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ==================== ACTION BUTTONS ==================== --}}
            <div class="mb-3 d-flex flex-wrap gap-2">
                <button onclick="toggleSection('stripping-section')" class="btn btn-sm btn-indigo">
                    <i class="fas fa-tools me-1"></i>Informasi Stripping
                </button>
                @can('update', $mwsPart)
                    <button onclick="toggleSection('attachment-section')" class="btn btn-sm btn-secondary">
                        <i class="fas fa-paperclip me-1"></i>Lampiran
                    </button>
                @endcan
                @if (isset($testCases))
                    <button onclick="toggleSection('testcase-section')" class="btn btn-sm btn-purple">
                        <i class="fas fa-flask me-1"></i>Test Case
                    </button>
                @endif
            </div>

            {{-- ==================== STRIPPING SECTION ==================== --}}
            <div id="stripping-section" class="card mb-4 d-none">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informasi Stripping</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="bg-light p-3 rounded">
                                <small class="d-block text-muted text-uppercase fw-bold mb-2">Tanggal Stripping</small>
                                <p class="mb-0 fw-medium">
                                    {{ $mwsPart->stripping_date ? \Carbon\Carbon::parse($mwsPart->stripping_date)->format('d/m/Y') : 'Belum diatur' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="bg-light p-3 rounded">
                                <small class="d-block text-muted text-uppercase fw-bold mb-2">Deadline Stripping</small>
                                <p class="mb-0 fw-medium">
                                    {{ $mwsPart->stripping_deadline ? \Carbon\Carbon::parse($mwsPart->stripping_deadline)->format('d/m/Y') : 'Belum diatur' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="bg-light p-3 rounded">
                                <small class="d-block text-muted text-uppercase fw-bold mb-2">Progress</small>
                                @php
                                    $strippingPct = $mwsPart->stripping_percentage ?? 100;
                                    $strippingColor = $strippingPct > 75 ? 'success' : ($strippingPct > 40 ? 'warning' : 'danger');
                                @endphp
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-{{ $strippingColor }}" style="width: {{ $strippingPct }}%"></div>
                                </div>
                                <small class="text-muted d-block mt-2">{{ $strippingPct }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ==================== ATTACHMENT SECTION ==================== --}}
            @can('update', $mwsPart)
                <div id="attachment-section" class="card mb-4 d-none">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Lampiran MWS</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3">
                            <input type="file" id="mws-attachment-input" multiple class="d-none" onchange="updateMwsFileName(this)">
                            <label for="mws-attachment-input" class="btn btn-outline-secondary btn-sm flex-grow-1">
                                <i class="fas fa-paperclip me-1"></i>
                                <span id="mws-file-name-display">Pilih file lampiran...</span>
                            </label>
                            <button onclick="uploadMwsAttachment('{{ $mwsPart->id }}')" class="btn btn-sm btn-primary">
                                <i class="fas fa-upload me-1"></i>Upload
                            </button>
                        </div>
                        <ul class="list-group" id="mws-attachment-list">
                            @forelse($mwsPart->attachments ?? [] as $att)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ $att['file_url'] }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file text-muted me-2"></i>{{ $att['original_filename'] }}
                                    </a>
                                    @can('update', $mwsPart)
                                        <button onclick="deleteMwsAttachment('{{ $mwsPart->id }}', '{{ $att['public_id'] }}')" class="btn btn-sm btn-link text-danger" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endcan
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">Belum ada lampiran MWS.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endcan

            {{-- ==================== GENERATE STEPS ==================== --}}
            @can('update', $mwsPart)
                @if ($mwsPart->steps->isEmpty())
                    <div class="mb-3">
                        <form action="{{ route('mws.generateSteps', $mwsPart->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-magic me-1"></i>Generate Steps dari Template
                            </button>
                        </form>
                    </div>
                @endif
            @endcan

            {{-- ==================== MAINTENANCE WORK SHEET ==================== --}}
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Maintenance Work Sheet</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        @can('update', $mwsPart)
                            <button id="smart-delete-btn" onclick="handleSmartDelete('{{ $mwsPart->id }}')" class="btn btn-sm btn-danger d-none">
                                <i class="fas fa-trash me-1"></i>Hapus Semua Step
                            </button>
                            <button onclick="addFirstStep('{{ $mwsPart->id }}')" class="btn btn-sm btn-success" title="Tambah Step Baru">
                                <i class="fas fa-plus me-1"></i>Add Step
                            </button>
                            <a href="{{ route('mws.print', $mwsPart->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-print me-1"></i>Print MWS
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    {{-- MWS Locked Banner --}}
                    @php
                        $isMechanic = auth()->user()->hasRole('mechanic');
                    @endphp
                    @if ($isMwsLocked && $isMechanic)
                        <div class="alert alert-warning mb-3 border-start border-3">
                            <i class="fas fa-lock me-2"></i>
                            Lembar kerja ini belum dapat diisi. Harap tunggu hingga Admin Approved bagian
                            <strong>"Prepared By"</strong> dan Superadmin Approved bagian
                            <strong>"Approved By"</strong>.
                        </div>
                    @endif

                    {{-- ==================== CONSUMABLES, MATERIALS & EXPENDABLES ==================== --}}
                    <div class="border rounded-3 p-3 mb-4">
                        <h6 class="fw-semibold mb-3">Consumables, Materials & Expendables</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Identification / References</th>
                                        <th class="text-center">Quantity</th>
                                        @can('update', $mwsPart)
                                            <th class="text-center">Aksi</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody id="consumables-tbody">
                                    @forelse($mwsPart->consumables as $consumable)
                                        <tr id="consumable-row-{{ $consumable->id }}">
                                            <td><span id="cons-name-{{ $consumable->id }}">{{ $consumable->name }}</span></td>
                                            <td><span id="cons-ident-{{ $consumable->id }}">{{ $consumable->identification ?? '-' }}</span></td>
                                            <td class="text-center"><span id="cons-qty-{{ $consumable->id }}">{{ $consumable->quantity }}</span></td>
                                            @can('update', $mwsPart)
                                                <td class="text-center" id="consumable-actions-{{ $consumable->id }}">
                                                    <button type="button" onclick="editConsumable('{{ $mwsPart->id }}', {{ $consumable->id }})" class="btn btn-outline-primary btn-sm me-1" title="Edit">Edit</button>
                                                    <button type="button" onclick="deleteConsumable('{{ $mwsPart->id }}', {{ $consumable->id }})" class="btn btn-outline-danger btn-sm" title="Hapus">Hapus</button>
                                                </td>
                                            @endcan
                                        </tr>
                                    @empty
                                        <tr id="consumables-empty-row">
                                            <td colspan="4" class="text-center text-muted py-3">Belum ada consumable.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @can('update', $mwsPart)
                                    <tfoot>
                                        <tr id="consumable-add-trigger" class="table-light">
                                            <td colspan="4" class="text-center py-2">
                                                <button type="button" onclick="toggleConsumableAdd(true)" class="btn btn-outline-primary btn-sm px-3">
                                                    <i class="fas fa-plus me-1"></i>Add Consumable
                                                </button>
                                            </td>
                                        </tr>
                                        <tr id="consumable-add-row" class="table-light d-none">
                                            <td><input type="text" id="new-cons-name" class="form-control form-control-sm" placeholder="Nama consumable..."></td>
                                            <td><input type="text" id="new-cons-ident" class="form-control form-control-sm" placeholder="Identification / References..."></td>
                                            <td><input type="text" id="new-cons-qty" class="form-control form-control-sm" placeholder="AR"></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" onclick="addConsumable('{{ $mwsPart->id }}')" class="btn btn-success btn-sm px-3">Save</button>
                                                    <button type="button" onclick="toggleConsumableAdd(false)" class="btn btn-secondary btn-sm px-3">Cancel</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                @endcan
                            </table>
                        </div>
                    </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto">
                    <table class="worksheet-table table table-bordered table-hover">
                        <thead>
                            <tr>
                                @can('update', $mwsPart)
                                    <th rowspan="2" class="col-select text-center p-2 align-middle">
                                        <input type="checkbox" id="select-all-steps" title="Pilih Semua">
                                    </th>
                                @endcan
                                <th rowspan="2"
                                    class="col-no text-center p-3 fw-bold text-secondary align-middle">NO</th>
                                <th rowspan="2"
                                    class="col-desc text-start p-3 fw-bold text-secondary align-middle">
                                    DESCRIPTION</th>
                                <th colspan="2"
                                    class="text-center p-3 fw-bold text-secondary">PLAN</th>
                                <th colspan="2"
                                    class="text-center p-3 fw-bold text-secondary">ACTUAL</th>
                                <th rowspan="2"
                                    class="col-tech text-center p-3 fw-bold text-secondary align-middle">TECH
                                </th>
                                <th rowspan="2"
                                    class="col-insp text-center p-3 fw-bold text-secondary align-middle">INSP
                                </th>
                                <th rowspan="2"
                                    class="col-action text-center p-3 fw-bold text-secondary align-middle">
                                    @can('update', $mwsPart)
                                        AKSI
                                    @else
                                        KETERANGAN
                                    @endcan
                                </th>
                                <th rowspan="2"
                                    class="col-attach text-center p-3 fw-bold text-secondary align-middle">
                                    LAMPIRAN PER STEP</th>
                                <th rowspan="2"
                                    class="col-status text-center p-3 fw-bold text-secondary align-middle">
                                    STATUS</th>
                            </tr>
                            <tr>
                                <th class="col-plan-man text-center p-3 fw-bold text-muted">MAN
                                </th>
                                <th class="col-plan-hrs text-center p-3 fw-bold text-muted">
                                    HOURS</th>
                                <th class="col-act-man text-center p-3 fw-bold text-muted">MAN
                                </th>
                                <th class="col-act-hrs text-center p-3 fw-bold text-muted">
                                    HOURS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mwsPart->steps->sortBy('no') as $step)
                                @php
                                    $rowClass = 'row-' . $step->status;
                                    $isCheck = strtolower(trim($step->description)) === 'check';
                                    $isFinal = strtolower(trim($step->description)) === 'final inspection';
                                    $stepMechanics = $step->mechanics ?? collect();
                                    $planIncomplete = !($step->plan_man && $step->plan_hours);
                                    $timerRunning = !empty($step->timer_start_time);
                                    $userNik = auth()->user()->nik ?? '';
                                    $mechanicNiks = $stepMechanics->pluck('nik')->toArray();
                                    $userInStep = in_array($userNik, $mechanicNiks);
                                    $techApproved = $step->tech === 'Approved';
                                    $stepCompleted = $step->status === 'completed';
                                    $stepInProgress = $step->status === 'in_progress';
                                @endphp
                                <tr id="step-row-{{ $step->no }}"
                                    class="step-row {{ $rowClass }} {{ $isCheck ? 'check-step-row' : '' }}">

                                    {{-- CHECKBOX --}}
                                    @can('update', $mwsPart)
                                        <td class="col-select text-center align-middle p-2">
                                            <input type="checkbox" class="step-checkbox" data-step-no="{{ $step->no }}">
                                        </td>
                                    @endcan

                                    {{-- NO --}}
                                    <td class="col-no text-center align-top p-2">
                                        <span class="step-no-badge">Step {{ $step->no }}</span>
                                    </td>

                                    {{-- DESCRIPTION --}}
                                    <td class="col-desc align-top p-3">
                                        <div id="step-desc-{{ $step->no }}" class="fw-bold text-dark fs-5 mb-3">
                                            {{ $step->description }}
                                        </div>

                                        @if ($step->caution)
                                            <div class="alert alert-warning py-3 px-3 mb-3 small lh-base">
                                                <span class="fw-bold text-uppercase me-1">Caution:</span>
                                                <span>{{ $step->caution }}</span>
                                            </div>
                                        @endif

                                        @if ($step->note)
                                            <div class="alert alert-info py-3 px-3 mb-3 small lh-base">
                                                <span class="fw-bold me-1">Note:</span>
                                                <span>{{ $step->note }}</span>
                                            </div>
                                        @endif

                                        @can('update', $mwsPart)
                                            <div class="d-flex flex-wrap align-items-center gap-3 mb-3 pb-2 border-bottom">
                                                <button onclick="toggleCautionEdit({{ $step->no }}, true)"
                                                    class="btn btn-link btn-sm text-warning p-0 text-decoration-none">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    {{ $step->caution ? 'Edit Caution' : '+ Caution' }}
                                                </button>
                                                <button onclick="toggleNoteEdit({{ $step->no }}, true)"
                                                    class="btn btn-link btn-sm text-primary p-0 text-decoration-none">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    {{ $step->note ? 'Edit Note' : '+ Note' }}
                                                </button>
                                            </div>

                                            <div id="caution-edit-{{ $step->no }}" class="hidden mb-3">
                                                <textarea id="caution-input-{{ $step->no }}" class="form-control form-control-sm" rows="2"
                                                    placeholder="Tulis teks CAUTION...">{{ $step->caution }}</textarea>
                                                <div class="d-flex gap-2 mt-2">
                                                    <button onclick="saveCaution('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="btn btn-warning btn-sm text-white">Simpan</button>
                                                    <button onclick="toggleCautionEdit({{ $step->no }}, false)"
                                                        class="btn btn-secondary btn-sm">Batal</button>
                                                </div>
                                            </div>

                                            <div id="note-edit-{{ $step->no }}" class="hidden mb-3">
                                                <textarea id="note-input-{{ $step->no }}" class="form-control form-control-sm" rows="2"
                                                    placeholder="Tulis teks Note...">{{ $step->note }}</textarea>
                                                <div class="d-flex gap-2 mt-2">
                                                    <button onclick="saveNote('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="btn btn-primary btn-sm">Simpan</button>
                                                    <button onclick="toggleNoteEdit({{ $step->no }}, false)"
                                                        class="btn btn-secondary btn-sm">Batal</button>
                                                </div>
                                            </div>
                                        @endcan

                                        <div id="details-list-{{ $step->no }}" class="mb-3">
                                            <ul class="mb-0 small ps-3 lh-lg">
                                                @foreach ($step->details ?? [] as $i => $detail)
                                                    <li id="detail-item-{{ $step->no }}-{{ $i }}" class="mb-2">
                                                        <span id="detail-text-{{ $step->no }}-{{ $i }}">{{ $detail }}</span>
                                                        @can('update', $mwsPart)
                                                            <button onclick="editDetail('{{ $mwsPart->id }}', {{ $step->no }}, {{ $i }})"
                                                                class="btn btn-link btn-sm p-0 ms-2 text-decoration-none">Edit</button>
                                                            <button onclick="deleteDetail('{{ $mwsPart->id }}', {{ $step->no }}, {{ $i }})"
                                                                class="btn btn-link btn-sm p-0 ms-1 text-danger text-decoration-none">Hapus</button>
                                                        @endcan
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        <div id="substeps-list-{{ $step->no }}" class="mb-3">
                                            @foreach ($step->subSteps as $sub)
                                                <div id="substep-item-{{ $sub->id }}"
                                                    class="d-flex align-items-start justify-content-between gap-3 border rounded-3 p-3 mb-2 bg-light">
                                                    <div class="small text-dark d-flex gap-2 lh-base">
                                                        <span class="fw-semibold text-secondary">{{ $sub->label }}.</span>
                                                        <span id="substep-text-{{ $sub->id }}">{{ $sub->description }}</span>
                                                    </div>
                                                    @can('update', $mwsPart)
                                                        <div class="d-flex gap-2 flex-shrink-0">
                                                            <button onclick="editSubStep('{{ $mwsPart->id }}', {{ $step->no }}, {{ $sub->id }})"
                                                                class="btn btn-link btn-sm p-0 text-decoration-none">Edit</button>
                                                            <button onclick="deleteSubStep('{{ $mwsPart->id }}', {{ $step->no }}, {{ $sub->id }})"
                                                                class="btn btn-link btn-sm p-0 text-danger text-decoration-none">Hapus</button>
                                                        </div>
                                                    @endcan
                                                </div>
                                            @endforeach
                                        </div>

                                        @can('update', $mwsPart)
                                            <div class="d-flex gap-2 mb-3">
                                                <input type="text" id="new-substep-input-{{ $step->no }}"
                                                    class="form-control form-control-sm py-2"
                                                    placeholder="Tambah sub-step (a, b, c)...">
                                                <button onclick="addSubStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="btn btn-primary btn-sm px-3 text-nowrap">+ Sub-step</button>
                                            </div>

                                            <div class="pt-3 border-top">
                                                <input type="text" id="new-detail-input-{{ $step->no }}"
                                                    class="form-control form-control-sm py-2"
                                                    placeholder="Tambah catatan baru...">
                                                <button onclick="addDetail('{{ $mwsPart->id }}', {{ $step->no }})" class="btn btn-outline-primary btn-sm mt-3 px-3">Tambah Catatan</button>
                                            </div>
                                        @endcan
                                    </td>

                                    {{-- PLAN MAN --}}
                                    <td class="col-plan-man align-top">
                                        @can('update', $mwsPart)
                                            <div id="plan-man-view-{{ $step->no }}"
                                                class="d-flex align-items-center justify-content-between gap-2">
                                                <span id="plan-man-text-{{ $step->no }}" class="small text-secondary">
                                                    {{ $step->plan_man ?? 'N/A' }}
                                                </span>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'man', true)"
                                                    class="btn btn-link btn-sm p-1"
                                                    title="Edit Plan Man">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                            <div id="plan-man-edit-{{ $step->no }}" class="d-none">
                                                <input type="text" id="plan-man-input-{{ $step->no }}"
                                                    value="{{ $step->plan_man }}"
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Contoh: 2">
                                                <button
                                                    onclick="savePlan('{{ $mwsPart->id }}', {{ $step->no }}, 'man')"
                                                    class="btn btn-success btn-sm w-100 mb-2">
                                                    <i class="fas fa-save me-1"></i> Simpan
                                                </button>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'man', false)"
                                                    class="btn btn-secondary btn-sm w-100">
                                                    Batal
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-sm text-center text-muted">{{ $step->plan_man ?? 'N/A' }}</p>
                                        @endcan
                                    </td>

                                    {{-- PLAN HOURS --}}
                                    <td class="col-plan-hrs align-top">
                                        @can('update', $mwsPart)
                                            <div id="plan-hours-view-{{ $step->no }}"
                                                class="d-flex align-items-center justify-content-between gap-2">
                                                <span id="plan-hours-text-{{ $step->no }}"
                                                    class="small text-secondary">
                                                    {{ $step->plan_hours ?? 'N/A' }}
                                                </span>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'hours', true)"
                                                    class="btn btn-link btn-sm p-1"
                                                    title="Edit Plan Hours">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                            <div id="plan-hours-edit-{{ $step->no }}" class="d-none">
                                                <input type="text" id="plan-hours-input-{{ $step->no }}"
                                                    value="{{ $step->plan_hours }}"
                                                    class="form-control form-control-sm mb-2"
                                                    placeholder="Contoh: 8:00">
                                                <button
                                                    onclick="savePlan('{{ $mwsPart->id }}', {{ $step->no }}, 'hours')"
                                                    class="btn btn-success btn-sm w-100 mb-2">
                                                    <i class="fas fa-save me-1"></i> Simpan
                                                </button>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'hours', false)"
                                                    class="mt-1 w-full px-2 py-1 bg-gray-400 hover:bg-gray-500 text-white text-xs rounded">
                                                    Batal
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-sm text-center text-muted">{{ $step->plan_hours ?? 'N/A' }}</p>
                                        @endcan
                                    </td>

                                    {{-- ACTUAL MAN --}}
                                    <td class="col-act-man align-top">
                                        <div class="space-y-1">
                                            <div class="font-semibold text-gray-800 text-sm">
                                                Total: <span class="text-blue-600">{{ count($mechanicNiks) }}</span>
                                                @if ($step->plan_man)
                                                    <span class="text-xs text-gray-500">/ {{ $step->plan_man }}</span>
                                                @endif
                                            </div>

                                            @if (count($mechanicNiks) > 0)
                                                <ul class="text-xs space-y-1">
                                                    @foreach ($stepMechanics as $mech)
                                                        <li
                                                            class="d-flex align-items-center justify-content-between bg-secondary-light p-2 rounded">
                                                            <span>{{ $mech->name }} - ({{ $mech->nik }})</span>
                                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                                                <button
                                                                    onclick="removeMechanicFromStep('{{ $mwsPart->id }}', {{ $step->no }}, '{{ $mech->nik }}')"
                                                                    class="btn btn-link btn-sm text-danger ms-2 p-0"
                                                                    title="Hapus Mekanik"><i class="fas fa-times"></i></button>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="small text-muted italic">Belum ada mekanik.</p>
                                            @endif

                                            {{-- Admin: Assign Mekanik Dropdown --}}
                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']) && !$techApproved)
                                                @if (count($mechanicNiks) < ($step->plan_man ?? 999))
                                                    <div class="mt-2">
                                                        <select id="assign-mechanic-select-{{ $step->no }}"
                                                            class="form-select form-select-sm w-100 mb-2">
                                                            <option value="">-- Pilih Mekanik --</option>
                                                            {{-- Ini perlu di-pass dari controller --}}
                                                            @foreach ($availableMechanics ?? [] as $mechanic)
                                                                <option value="{{ $mechanic->nik }}">
                                                                    {{ $mechanic->name }} ({{ $mechanic->nik }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <button
                                                            onclick="assignMechanicToStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="btn btn-primary btn-sm w-100">
                                                            <i class="fas fa-user-plus me-1"></i> Assign
                                                        </button>
                                                    </div>
                                                @else
                                                    <p class="small text-warning mt-1">Slot mekanik penuh
                                                        ({{ $step->plan_man }}).</p>
                                                @endif
                                            @endif

                                            {{-- Mekanik: Sign On Sendiri --}}
                                            @if ($isMechanic && !$userInStep && !$techApproved)
                                                <button
                                                    onclick="addMeToStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="btn btn-success btn-sm w-100 {{ $isMwsLocked || $planIncomplete || count($mechanicNiks) >= ($step->plan_man ?? 999) ? 'disabled opacity-50' : '' }}"
                                                    @if ($isMwsLocked) disabled title="MWS terkunci."
                                                    @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu."
                                                    @elseif(count($mechanicNiks) >= ($step->plan_man ?? 999)) disabled title="Slot mekanik sudah penuh." @endif>
                                                    <i class="fas fa-sign-in-alt me-1"></i> Sign On
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- ACTUAL HOURS --}}
                                    <td class="col-act-hrs align-top">
                                        <div class="d-flex flex-column align-items-center gap-2">
                                            <input type="hidden" id="hours-{{ $step->no }}"
                                                value="{{ $step->hours }}">
                                            <span id="hours-display-{{ $step->no }}"
                                                class="font-monospace text-larger fw-bold text-dark"
                                                @if ($timerRunning) data-start-time="{{ $step->timer_start_time }}"
                                            data-initial-hours="{{ $step->hours }}" @endif>
                                                {{ $step->hours }}
                                            </span>

                                            @if ($isMechanic && $userInStep && !$techApproved)
                                                @if ($timerRunning)
                                                    <button
                                                        onclick="stopTimer('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="btn btn-danger btn-sm w-100 {{ $isMwsLocked ? 'disabled opacity-50' : '' }}"
                                                        @if ($isMwsLocked) disabled @endif>
                                                        <i class="fas fa-stop me-1"></i> Stop
                                                    </button>
                                                @else
                                                    <button
                                                        onclick="startTimer('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="btn btn-primary btn-sm w-100 {{ $isMwsLocked || $planIncomplete ? 'disabled opacity-50' : '' }}"
                                                        @if ($isMwsLocked) disabled title="MWS terkunci"
                                            @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu." @endif>
                                                        <i class="fas fa-play me-1"></i> Start
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    {{-- TECH --}}
                                    <td class="col-tech align-top">
                                        @if ($isMechanic && $userInStep && !$techApproved)
                                            <div class="d-flex align-items-center justify-content-center" style="min-height: 40px;">
                                                <button
                                                    onclick="approveStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="btn btn-success btn-sm {{ $isMwsLocked || $timerRunning || $planIncomplete ? 'disabled opacity-50' : '' }}"
                                                    @if ($isMwsLocked) disabled title="MWS terkunci."
                                            @elseif($timerRunning) disabled title="Hentikan timer terlebih dahulu."
                                            @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu." @endif>
                                                    Approve
                                                </button>
                                            </div>
                                        @else
                                            <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 40px; gap: 0.5rem;">
                                                <span class="small fw-bold text-dark text-center">
                                                    {{ $techApproved ? 'Approved' : 'N/A' }}
                                                </span>
                                                @if ($techApproved)
                                                    @can('update', $mwsPart)
                                                        <button
                                                            onclick="cancelApproval('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="btn btn-danger btn-sm">
                                                            Unapprove
                                                        </button>
                                                    @endcan
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- INSP --}}
                                    <td class="col-insp align-top">
                                        @if (auth()->user()->hasRole('quality1') && $stepInProgress && $techApproved)
                                            @if ($isFinal)
                                                {{-- Final Inspection --}}
                                                <div class="flex flex-col space-y-2 p-1"
                                                    id="final-inspection-controls-{{ $step->no }}">
                                                    <select id="status-s-us-select-{{ $step->no }}"
                                                        class="w-full border rounded px-2 py-1 text-xs"
                                                        onchange="enableFinalApprove({{ $step->no }})">
                                                        <option value="" selected disabled>-- Pilih Status --
                                                        </option>
                                                        <option value="RAI">RAI (Release Authorize Inspector)</option>
                                                        <option value="S">S (Serviceable)</option>
                                                        <option value="U/S">U/S (Unserviceable)</option>
                                                        <option value="BEYOND REPAIR">Beyond Repair</option>
                                                    </select>
                                                    <button id="final-approve-btn-{{ $step->no }}"
                                                        onclick="finishFinalInspection('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="px-2 py-1.5 bg-green-600 text-white rounded text-xs font-semibold opacity-50 cursor-not-allowed transition-all"
                                                        disabled title="Pilih status terlebih dahulu">
                                                        Approve & Finish
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center min-h-[40px]">
                                                    <button
                                                        onclick="finishStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm font-medium transition-colors"
                                                        @if ($isMwsLocked) disabled title="MWS terkunci" @endif>
                                                        Approve
                                                    </button>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center">
                                                <span class="text-sm font-semibold text-gray-900">
                                                    {{ $stepCompleted ? 'Approved' : 'N/A' }}
                                                </span>
                                                @if ($stepCompleted)
                                                    @can('update', $mwsPart)
                                                        <br>
                                                        <button
                                                            onclick="cancelFinishStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="mt-1 px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors">
                                                            Unapprove
                                                        </button>
                                                    @endcan
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                    {{-- AKSI / KETERANGAN --}}
                                    <td class="col-action text-center align-top">
                                        @can('update', $mwsPart)
                                            <div class="flex flex-col items-center space-y-1">
                                                <div class="text-xs text-gray-500 mb-1">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium badge-{{ $step->status }}">
                                                        {{ ucfirst($step->status) }}
                                                    </span>
                                                </div>
                                                <div class="flex space-x-1">
                                                    <button
                                                        onclick="editStepDescription('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        title="Edit Deskripsi"
                                                        class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors">
                                                        <i class="fas fa-edit text-xs"></i>
                                                    </button>
                                                    <button
                                                        onclick="deleteStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        title="Hapus Step"
                                                        class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors">
                                                        <i class="fas fa-trash-alt text-xs"></i>
                                                    </button>
                                                    <button
                                                        onclick="insertStepAfter('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        title="Sisipkan Step Setelah Ini"
                                                        class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded transition-colors">
                                                        <i class="fas fa-plus-circle text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif($isMechanic)
                                            <div class="flex flex-col items-center space-y-1">
                                                <span class="text-xs text-gray-500">{{ $step->status }}</span>
                                                @if ($step->status === 'pending' && !$techApproved)
                                                    <span class="text-xs text-gray-400">Klik Approve untuk melanjutkan</span>
                                                @endif
                                            </div>
                                        @elseif(auth()->user()->hasRole('quality1') || auth()->user()->hasRole('quality2'))
                                            <div class="flex flex-col items-center space-y-1">
                                                <span class="text-xs text-gray-500">{{ $step->status }}</span>
                                                @if ($stepInProgress)
                                                    <span class="text-xs text-gray-400">Klik Finish untuk menyelesaikan</span>
                                                @endif
                                            </div>
                                        @elseif(auth()->user()->hasRole('customer'))
                                            <div class="flex items-center justify-center h-full">
                                                @if ($stepInProgress)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">In
                                                        Progress</span>
                                                @elseif($stepCompleted)
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Completed</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pending</span>
                                                @endif
                                            </div>
                                        @endcan
                                    </td>

                                    {{-- LAMPIRAN PER STEP --}}
                                    <td class="col-attach align-top">
                                        <div class="space-y-1">
                                            @if (!empty($step->attachments) && count($step->attachments) > 0)
                                                <ul class="text-xs space-y-1">
                                                    @foreach ($step->attachments as $attachment)
                                                        <li class="flex flex-col bg-gray-100 p-1 rounded">
                                                            <div class="flex items-center justify-between">
                                                                <a href="{{ $attachment['file_url'] }}" target="_blank"
                                                                    class="text-blue-600 hover:underline truncate flex items-center space-x-1"
                                                                    title="{{ $attachment['original_filename'] }}">
                                                                    <i class="fas fa-paperclip"></i>
                                                                    <span>{{ Str::limit($attachment['original_filename'], 15) }}</span>
                                                                </a>
                                                                @if ($isMechanic)
                                                                    <button
                                                                        onclick="deleteStepAttachment('{{ $mwsPart->id }}', {{ $step->no }}, '{{ $attachment['public_id'] }}')"
                                                                        class="ml-1 text-red-500 hover:text-red-700 font-bold"
                                                                        title="Hapus">&times;</button>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-xs text-gray-500 italic">Belum ada lampiran.</p>
                                            @endif

                                            @if ($isMechanic)
                                                <div class="mt-2 pt-2 border-t border-gray-100">
                                                    <div class="flex items-center space-x-1">
                                                        <label for="step-attachment-input-{{ $step->no }}"
                                                            class="cursor-pointer p-1.5 bg-white border border-gray-300 hover:border-blue-400 rounded text-gray-500 hover:text-blue-500 transition-colors">
                                                            <i class="fas fa-paperclip text-xs"></i>
                                                        </label>
                                                        <input type="file"
                                                            id="step-attachment-input-{{ $step->no }}"
                                                            class="hidden" multiple
                                                            onchange="updateFileName(this, {{ $step->no }})">
                                                        <button
                                                            onclick="uploadStepAttachment('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="flex-1 px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-bold transition-colors
                                                           {{ $isMwsLocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                            @if ($isMwsLocked) disabled @endif>
                                                            <i class="fas fa-upload mr-1"></i> Unggah
                                                        </button>
                                                    </div>
                                                    <span id="file-name-display-{{ $step->no }}"
                                                        class="text-xs text-gray-500 italic mt-1 block truncate">
                                                        Pilih file...
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- STATUS ICON --}}
                                    <td class="col-status text-center align-middle">
                                        @if ($stepCompleted)
                                            <i class="fas fa-check-circle text-blue-500 text-xl" title="Completed"></i>
                                        @elseif($stepInProgress)
                                            <i class="fas fa-clock text-green-500 text-xl" title="In Progress"></i>
                                        @else
                                            <i class="fas fa-exclamation-circle text-red-500 text-xl" title="Pending"></i>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-12 text-gray-500">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                                        Belum ada step. Tambahkan step baru atau generate dari template.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ==================== SIGN SECTION ==================== --}}
            <div class="row">

                {{-- Tanggal & Durasi --}}
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Tanggal &amp; Durasi Pengerjaan</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="text" readonly value="{{ $mwsPart->start_date ? \Carbon\Carbon::parse($mwsPart->start_date)->format('d/m/Y') : '' }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Finish Date</label>
                                <input type="text" readonly value="{{ $mwsPart->finish_date ? \Carbon\Carbon::parse($mwsPart->finish_date)->format('d/m/Y') : '' }}" placeholder="dd/mm/yyyy" class="form-control">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Total Durasi Pengerjaan</label>
                                <p class="fw-semibold" id="total-duration">{{ $mwsPart->total_duration ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approved MWS --}}
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Approved Maintenance Work Sheet</h5>
                        </div>
                        <div class="card-body">

                            {{-- Prepared By --}}
                            <div class="p-3 mb-3 rounded border {{ $mwsPart->preparedBy ? 'border-success bg-light-success' : 'border-light bg-light' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-2">Prepared By</h6>
                                        @if ($mwsPart->preparedBy)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-user-check text-success me-2"></i>
                                                <p class="mb-0 fw-medium small">{{ $mwsPart->preparedBy }}</p>
                                            </div>
                                            <small class="text-muted">{{ $mwsPart->preparedAt ? \Carbon\Carbon::parse($mwsPart->preparedAt)->format('d/m/Y H:i') : '' }}</small>
                                        @else
                                            <p class="mb-0 text-muted small">Menunggu Approved...</p>
                                        @endif
                                    </div>
                                    @if (!$mwsPart->preparedBy && in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                        <button onclick="signDocument('{{ $mwsPart->id }}', 'prepared')" class="btn btn-sm btn-primary ms-2">
                                            <i class="fas fa-signature me-1"></i> Sign
                                        </button>
                                    @elseif($mwsPart->preparedBy)
                                        <div class="d-flex align-items-center ms-2">
                                            <i class="fas fa-check-circle text-success"></i>
                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                                <button onclick="cancelSignature('{{ $mwsPart->id }}', 'prepared', 'Anda yakin ingin membatalkan tanda tangan Prepared By?')" class="btn btn-link btn-sm text-danger ms-2">Batal</button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Approved By --}}
                            <div class="p-3 mb-3 rounded border {{ $mwsPart->approvedBy ? 'border-success bg-light-success' : 'border-light bg-light' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-2">Approved By</h6>
                                        @if ($mwsPart->approvedBy)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-user-check text-success me-2"></i>
                                                <p class="mb-0 fw-medium small">{{ $mwsPart->approvedBy }}</p>
                                            </div>
                                            <small class="text-muted">{{ $mwsPart->approvedAt ? \Carbon\Carbon::parse($mwsPart->approvedAt)->format('d/m/Y H:i') : '' }}</small>
                                        @else
                                            <p class="mb-0 text-muted small">Menunggu Approved...</p>
                                        @endif
                                    </div>
                                    @if (!$mwsPart->approvedBy && in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                        <button onclick="signDocument('{{ $mwsPart->id }}', 'approved')" class="btn btn-sm btn-danger ms-2">
                                            <i class="fas fa-signature me-1"></i> Sign
                                        </button>
                                    @elseif($mwsPart->approvedBy)
                                        <div class="d-flex align-items-center ms-2">
                                            <i class="fas fa-check-circle text-success"></i>
                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                                <button onclick="cancelSignature('{{ $mwsPart->id }}', 'approved', 'Anda yakin ingin membatalkan tanda tangan Approved By?')" class="btn btn-link btn-sm text-danger ms-2">Batal</button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Verified By --}}
                            <div class="p-3 rounded border {{ $mwsPart->verifiedBy ? 'border-success bg-light-success' : 'border-light bg-light' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="fw-semibold mb-2">Verified By</h6>
                                        @if ($mwsPart->verifiedBy)
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-user-check text-success me-2"></i>
                                                <p class="mb-0 fw-medium small">{{ $mwsPart->verifiedBy }}</p>
                                            </div>
                                            <small class="text-muted">{{ $mwsPart->verifiedAt ? \Carbon\Carbon::parse($mwsPart->verifiedAt)->format('d/m/Y H:i') : '' }}</small>
                                        @else
                                            <p class="mb-0 text-muted small">Menunggu Verified Quality...</p>
                                        @endif
                                    </div>
                                    @if (!$mwsPart->verifiedBy && (auth()->user()->role ?? '') === 'quality2')
                                        <button onclick="signDocument('{{ $mwsPart->id }}', 'verified')" class="btn btn-sm btn-info ms-2">
                                            <i class="fas fa-signature me-1"></i> Sign
                                        </button>
                                    @elseif($mwsPart->verifiedBy)
                                        <div class="d-flex align-items-center ms-2">
                                            <i class="fas fa-check-circle text-success"></i>
                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                                <button onclick="cancelSignature('{{ $mwsPart->id }}', 'verified', 'Anda yakin ingin membatalkan tanda tangan Verified By?')" class="btn btn-link btn-sm text-danger ms-2">Batal</button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- end main container --}}
    </div>{{-- end min-h-screen --}}
@endsection
@push('scripts')
    <script src="{{ asset('js/info_mws_logic.js') }}"></script>
@endpush
