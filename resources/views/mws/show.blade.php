@extends('layouts.app')

@section('title', 'MWS ' . $mwsPart->part_number . ' - Sistem Aircraft Maintenance')

@push('styles')
    <style>
        /* ===== LAYOUT & TABLE ===== */
        .worksheet-table {
            table-layout: fixed;
            width: 100%;
            min-width: 1200px;
        }

        .worksheet-table th,
        .worksheet-table td {
            vertical-align: top;
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
        }

        .col-select {
            width: 45px;
        }

        .col-no {
            width: 50px;
        }

        .col-desc {
            width: 28%;
        }

        .col-plan-man,
        .col-plan-hrs,
        .col-act-man,
        .col-act-hrs {
            width: 9%;
            min-width: 110px;
        }

        .col-tech {
            width: 10%;
            min-width: 120px;
        }

        .col-insp {
            width: 8%;
            min-width: 90px;
        }

        .col-status {
            width: 7%;
            min-width: 80px;
        }

        .col-action {
            width: 10%;
            min-width: 120px;
        }

        .col-attach {
            width: 12%;
            min-width: 130px;
        }

        .tech-cell {
            max-width: 150px !important;
            min-width: 120px !important;
            width: 150px !important;
        }

        .tech-cell textarea,
        .tech-cell span {
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
            hyphens: auto;
            line-height: 1.3;
        }

        /* ===== ROW STATUS COLORS ===== */
        .row-completed {
            background-color: #eff6ff;
        }

        .row-in_progress {
            background-color: #f0fdf4;
        }

        .row-pending {
            background-color: #fef2f2;
        }

        .worksheet-table tbody tr:hover {
            background-color: #f9fafb !important;
        }

        /* ===== STRIPPING NOTIFICATION ===== */
        #stripping-notification {
            position: fixed;
            top: 5rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            color: white;
            z-index: 50;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
            min-width: 300px;
            max-width: 400px;
        }

        #stripping-notification.warning {
            background-color: #f59e0b;
        }

        #stripping-notification.critical {
            background-color: #ef4444;
            animation: pulse 2s infinite;
        }

        #stripping-notification.safe {
            background-color: #10b981;
        }

        .stripping-progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, .3);
            border-radius: 4px;
            margin: .5rem 0;
            overflow: hidden;
        }

        .stripping-progress-fill {
            height: 100%;
            background: white;
            border-radius: 4px;
            transition: width .3s ease;
        }

        /* ===== TOAST / NOTIFICATION ===== */
        #toast-notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            color: white;
            z-index: 100;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
            min-width: 320px;
            max-width: 400px;
            animation: slideIn .3s ease-out;
            display: none;
        }

        #toast-notification.success {
            background-color: #10b981;
        }

        #toast-notification.error {
            background-color: #ef4444;
        }

        #toast-notification.info {
            background-color: #3b82f6;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .7;
            }
        }

        /* ===== STRIPPING WARNING ROWS ===== */
        .stripping-warning {
            background-color: #fef3cd !important;
            border-left: 4px solid #f59e0b;
        }

        .stripping-critical {
            background-color: #fee2e2 !important;
            border-left: 4px solid #ef4444;
            animation: subtle-pulse 3s infinite;
        }

        @keyframes subtle-pulse {

            0%,
            100% {
                background-color: #fee2e2;
            }

            50% {
                background-color: #fecaca;
            }
        }

        /* ===== PLAN INLINE EDIT ===== */
        .plan-edit-area {
            display: none;
        }

        /* ===== BADGE STATUS ===== */
        .badge-completed {
            background: #3b82f6;
            color: #fff;
        }

        .badge-in_progress {
            background: #10b981;
            color: #fff;
        }

        .badge-pending {
            background: #ef4444;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gray-50">

        {{-- ==================== STRIPPING NOTIFICATION ==================== --}}
        <div id="stripping-notification" style="display:none;">
            <div class="flex items-start space-x-2">
                <div class="flex-shrink-0"><i id="stripping-icon" class="fas fa-exclamation-triangle text-xl"></i></div>
                <div class="flex-1">
                    <h4 class="font-bold text-sm mb-1">Peringatan Stripping</h4>
                    <p id="stripping-message" class="text-sm mb-2"></p>
                    <div class="stripping-progress-bar">
                        <div id="stripping-progress-fill" class="stripping-progress-fill" style="width:100%"></div>
                    </div>
                    <div class="flex justify-between text-xs mt-1">
                        <span id="stripping-percentage">100%</span>
                        <span id="stripping-deadline"></span>
                    </div>
                </div>
                <button onclick="dismissStrippingNotification()" class="flex-shrink-0 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- ==================== TOAST ==================== --}}
        <div id="toast-notification">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-2">
                    <i id="toast-icon" class="fas fa-check-circle text-xl mt-0.5"></i>
                    <span id="toast-message" class="text-sm font-medium"></span>
                </div>
                <button onclick="dismissToast()" class="ml-4 text-white opacity-70 hover:opacity-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- ==================== TOP HEADER ==================== --}}
        <div class="bg-white shadow-sm border-b sticky top-0 z-40">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="mr-4 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left text-gray-600"></i>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">
                                Customer: <span>{{ $mwsPart->customer->company_name ?? '-' }}</span>
                            </h1>
                            <p class="text-gray-600">Serial Number: <span>{{ $mwsPart->serial_number }}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @php
                            $statusClass = match ($mwsPart->status) {
                                'completed' => 'bg-blue-500 text-white',
                                'in_progress' => 'bg-green-500 text-white',
                                default => 'bg-red-500 text-white',
                            };
                            $statusLabel = match ($mwsPart->status) {
                                'completed' => 'Completed',
                                'in_progress' => 'In Progress',
                                default => 'Pending',
                            };
                        @endphp
                        <span class="px-4 py-2 rounded-full text-sm font-medium {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            {{-- ==================== INFORMASI MWS ==================== --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">Informasi MWS</h2>
                    <div class="flex items-center space-x-3">
                        @can('update', $mwsPart)
                            <button onclick="toggleEditMwsInfo(true)"
                                class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-edit"></i><span>Edit</span>
                            </button>
                            <button onclick="confirmDuplicateMws('{{ $mwsPart->id }}')"
                                class="flex items-center space-x-1 text-purple-600 hover:text-purple-800 text-sm font-medium">
                                <i class="fas fa-copy"></i><span>Duplicate</span>
                            </button>
                        @endcan
                    </div>
                </div>

                {{-- VIEW MODE --}}
                <div id="mws-info-view" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    @php
                        $infoFields = [
                            ['label' => 'Tittle / Part Name', 'value' => $mwsPart->title],
                            ['label' => 'Part Number', 'value' => $mwsPart->part_number],
                            ['label' => 'Ref', 'value' => $mwsPart->ref ?? 'N/A'],
                            ['label' => 'Component Order', 'value' => $mwsPart->job_type ?? 'N/A'],
                            ['label' => 'Customer', 'value' => $mwsPart->customer->company_name ?? '-'],
                            ['label' => 'A/C Type', 'value' => $mwsPart->ac_type ?? 'N/A'],
                            ['label' => 'Serial Number', 'value' => $mwsPart->serial_number],
                            ['label' => 'WBS No.', 'value' => $mwsPart->wbs_no ?? 'N/A'],
                            ['label' => 'Worksheet No.', 'value' => $mwsPart->worksheet_no ?? 'N/A'],
                            ['label' => 'IWO No.', 'value' => $mwsPart->iwo_no],
                            ['label' => 'Shop Area', 'value' => $mwsPart->shop_area ?? 'N/A'],
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
                        <div class="border border-gray-100 bg-gray-50 p-3 rounded-lg">
                            <label
                                class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $field['label'] }}</label>
                            <p class="text-gray-800 font-medium">{{ $field['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- EDIT MODE --}}
                <form id="mws-info-edit" class="hidden" onsubmit="saveMwsInfo(event)">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        @php
                            $editFields = [
                                ['name' => 'title', 'label' => 'Tittle / Part Name', 'value' => $mwsPart->title],
                                ['name' => 'part_number', 'label' => 'Part Number', 'value' => $mwsPart->part_number],
                                ['name' => 'ref', 'label' => 'Ref', 'value' => $mwsPart->ref],
                                ['name' => 'job_type', 'label' => 'Component Order', 'value' => $mwsPart->job_type],
                                ['name' => 'ac_type', 'label' => 'A/C Type', 'value' => $mwsPart->ac_type],
                                [
                                    'name' => 'serial_number',
                                    'label' => 'Serial Number',
                                    'value' => $mwsPart->serial_number,
                                ],
                                ['name' => 'wbs_no', 'label' => 'WBS No.', 'value' => $mwsPart->wbs_no],
                                [
                                    'name' => 'worksheet_no',
                                    'label' => 'Worksheet No.',
                                    'value' => $mwsPart->worksheet_no,
                                ],
                                ['name' => 'iwo_no', 'label' => 'IWO No.', 'value' => $mwsPart->iwo_no],
                                ['name' => 'shop_area', 'label' => 'Shop Area', 'value' => $mwsPart->shop_area],
                                ['name' => 'revision', 'label' => 'Revision', 'value' => $mwsPart->revision],
                                ['name' => 'zone', 'label' => 'Zone', 'value' => $mwsPart->zone],
                                [
                                    'name' => 'start_date',
                                    'label' => 'Start Date',
                                    'value' => $mwsPart->start_date,
                                    'type' => 'date',
                                ],
                            ];
                        @endphp
                        @foreach ($editFields as $f)
                            <div class="border border-blue-200 bg-blue-50 p-3 rounded-lg">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $f['label'] }}</label>
                                <input type="{{ $f['type'] ?? 'text' }}" name="{{ $f['name'] }}"
                                    value="{{ $f['value'] }}"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent">
                            </div>
                        @endforeach
                    </div>
                    <div class="flex space-x-2 mt-4">
                        <button type="submit"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded transition-colors">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                        <button type="button" onclick="toggleEditMwsInfo(false)"
                            class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white text-sm font-medium rounded transition-colors">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                    </div>
                </form>
            </div>

            {{-- ==================== ACTION BUTTONS ==================== --}}
            <div class="flex flex-wrap gap-3">
                <button onclick="toggleSection('stripping-section')"
                    class="flex items-center space-x-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                    <i class="fas fa-tools"></i><span>Informasi Stripping</span>
                </button>

                @can('update', $mwsPart)
                    <button onclick="toggleSection('attachment-section')"
                        class="flex items-center space-x-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                        <i class="fas fa-paperclip"></i><span>Lampiran</span>
                    </button>
                @endcan

                @if (isset($testCases))
                    <button onclick="toggleSection('testcase-section')"
                        class="flex items-center space-x-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                        <i class="fas fa-flask"></i><span>Test Case</span>
                    </button>
                @endif
            </div>

            {{-- ==================== STRIPPING SECTION ==================== --}}
            <div id="stripping-section" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Stripping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div class="border p-3 rounded-lg bg-gray-50">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal
                            Stripping</label>
                        <p class="font-medium text-gray-800">
                            {{ $mwsPart->stripping_date ? \Carbon\Carbon::parse($mwsPart->stripping_date)->format('d/m/Y') : 'Belum diatur' }}
                        </p>
                    </div>
                    <div class="border p-3 rounded-lg bg-gray-50">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Deadline
                            Stripping</label>
                        <p class="font-medium text-gray-800">
                            {{ $mwsPart->stripping_deadline ? \Carbon\Carbon::parse($mwsPart->stripping_deadline)->format('d/m/Y') : 'Belum diatur' }}
                        </p>
                    </div>
                    <div class="border p-3 rounded-lg bg-gray-50">
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Progress</label>
                        @php
                            $strippingPct = $mwsPart->stripping_percentage ?? 100;
                            $strippingColor =
                                $strippingPct > 75
                                    ? 'bg-green-500'
                                    : ($strippingPct > 40
                                        ? 'bg-yellow-500'
                                        : 'bg-red-500');
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="{{ $strippingColor }} h-2 rounded-full transition-all"
                                style="width: {{ $strippingPct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $strippingPct }}%</p>
                    </div>
                </div>
            </div>

            {{-- ==================== ATTACHMENT SECTION ==================== --}}
            @can('update', $mwsPart)
                <div id="attachment-section" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Lampiran MWS</h3>
                    <div class="flex items-center space-x-3 mb-4">
                        <input type="file" id="mws-attachment-input" multiple class="hidden"
                            onchange="updateMwsFileName(this)">
                        <label for="mws-attachment-input"
                            class="cursor-pointer flex items-center space-x-2 px-4 py-2 bg-white border-2 border-dashed border-gray-300 hover:border-blue-400 rounded-lg text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-paperclip"></i>
                            <span id="mws-file-name-display">Pilih file lampiran...</span>
                        </label>
                        <button onclick="uploadMwsAttachment('{{ $mwsPart->id }}')"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors">
                            <i class="fas fa-upload mr-1"></i> Upload
                        </button>
                    </div>
                    <ul class="space-y-2" id="mws-attachment-list">
                        @forelse($mwsPart->attachments ?? [] as $att)
                            <li class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg border">
                                <a href="{{ $att['file_url'] }}" target="_blank"
                                    class="text-blue-600 hover:underline text-sm flex items-center space-x-2">
                                    <i class="fas fa-file text-gray-400"></i>
                                    <span>{{ $att['original_filename'] }}</span>
                                </a>
                                @can('update', $mwsPart)
                                    <button onclick="deleteMwsAttachment('{{ $mwsPart->id }}', '{{ $att['public_id'] }}')"
                                        class="text-red-500 hover:text-red-700 text-sm font-bold ml-2" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                @endcan
                            </li>
                        @empty
                            <li class="text-gray-500 text-sm italic">Belum ada lampiran MWS.</li>
                        @endforelse
                    </ul>
                </div>
            @endcan

            {{-- ==================== GENERATE STEPS ==================== --}}
            @can('update', $mwsPart)
                @if ($mwsPart->steps->isEmpty())
                    <div class="flex">
                        <form action="{{ route('mws.generateSteps', $mwsPart->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center space-x-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                <i class="fas fa-magic"></i><span>Generate Steps dari Template</span>
                            </button>
                        </form>
                    </div>
                @endif
            @endcan

            {{-- ==================== MAINTENANCE WORK SHEET ==================== --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center flex-wrap gap-3">
                    <h2 class="text-xl font-semibold text-gray-800">Maintenance Work Sheet</h2>
                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                        @can('update', $mwsPart)
                            {{-- Smart Delete (bulk) --}}
                            <button id="smart-delete-btn" onclick="handleSmartDelete('{{ $mwsPart->id }}')"
                                class="hidden px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                                <i class="fas fa-trash mr-1"></i> Hapus Semua Step
                            </button>

                            {{-- Add Step --}}
                            <button onclick="addFirstStep('{{ $mwsPart->id }}')"
                                class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
                                title="Tambah Step Baru">
                                <i class="fas fa-plus"></i>
                            </button>

                            {{-- Print --}}
                            <a href="{{ route('mws.print', $mwsPart->id) }}" target="_blank"
                                class="flex items-center space-x-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                <i class="fas fa-print"></i><span>Print MWS</span>
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- MWS Locked Banner --}}
                @php
                    $isMwsLocked = !($mwsPart->preparedBy && $mwsPart->approvedBy);
                    $isMechanic = auth()->user()->hasRole('mechanic');
                @endphp
                @if ($isMwsLocked && $isMechanic)
                    <div
                        class="mx-4 mt-4 p-4 bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500 rounded-md shadow-sm">
                        <div class="flex items-start">
                            <i class="fas fa-lock text-yellow-500 mt-0.5 mr-3"></i>
                            <p class="text-sm">
                                Lembar kerja ini belum dapat diisi. Harap tunggu hingga Admin Approved bagian
                                <strong>"Prepared By"</strong> dan Superadmin Approved bagian
                                <strong>"Approved By"</strong>.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- TABLE --}}
                <div class="overflow-x-auto">
                    <table class="worksheet-table">
                        <thead class="bg-gray-100">
                            <tr>
                                @can('update', $mwsPart)
                                    <th rowspan="2" class="col-select text-center p-2 align-middle">
                                        <input type="checkbox" id="select-all-steps" title="Pilih Semua">
                                    </th>
                                @endcan
                                <th rowspan="2"
                                    class="col-no text-center p-3 text-sm font-semibold text-gray-700 align-middle">NO</th>
                                <th rowspan="2"
                                    class="col-desc text-left p-3 text-sm font-semibold text-gray-700 align-middle">
                                    DESCRIPTION</th>
                                <th colspan="2"
                                    class="text-center p-3 text-sm font-semibold text-gray-700 bg-blue-100">PLAN</th>
                                <th colspan="2"
                                    class="text-center p-3 text-sm font-semibold text-gray-700 bg-green-100">ACTUAL</th>
                                <th rowspan="2"
                                    class="col-tech text-center p-3 text-sm font-semibold text-gray-700 align-middle">TECH
                                </th>
                                <th rowspan="2"
                                    class="col-insp text-center p-3 text-sm font-semibold text-gray-700 align-middle">INSP
                                </th>
                                <th rowspan="2"
                                    class="col-action text-center p-3 text-sm font-semibold text-gray-700 align-middle">
                                    @can('update', $mwsPart)
                                        AKSI
                                    @else
                                        KETERANGAN
                                    @endcan
                                </th>
                                <th rowspan="2"
                                    class="col-attach text-center p-3 text-sm font-semibold text-gray-700 align-middle">
                                    LAMPIRAN PER STEP</th>
                                <th rowspan="2"
                                    class="col-status text-center p-3 text-sm font-semibold text-gray-700 align-middle">
                                    STATUS</th>
                            </tr>
                            <tr>
                                <th class="col-plan-man text-center p-3 text-sm font-semibold text-gray-600 bg-blue-50">MAN
                                </th>
                                <th class="col-plan-hrs text-center p-3 text-sm font-semibold text-gray-600 bg-blue-50">
                                    HOURS</th>
                                <th class="col-act-man text-center p-3 text-sm font-semibold text-gray-600 bg-green-50">MAN
                                </th>
                                <th class="col-act-hrs text-center p-3 text-sm font-semibold text-gray-600 bg-green-50">
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
                                    class="{{ $rowClass }} {{ $isCheck ? 'check-step-row' : '' }} hover:bg-gray-50">

                                    {{-- CHECKBOX --}}
                                    @can('update', $mwsPart)
                                        <td class="col-select text-center align-middle p-2">
                                            <input type="checkbox" class="step-checkbox" data-step-no="{{ $step->no }}">
                                        </td>
                                    @endcan

                                    {{-- NO --}}
                                    <td class="col-no text-center font-medium align-top">{{ $step->no }}</td>

                                    {{-- DESCRIPTION --}}
                                    <td class="col-desc text-sm align-top">
                                        <div id="step-desc-{{ $step->no }}" class="font-semibold text-gray-800">
                                            {{ $step->description }}
                                        </div>

                                        {{-- Details List --}}
                                        <div id="details-list-{{ $step->no }}" class="mt-2 pl-4">
                                            <ul class="list-disc list-inside text-gray-600 space-y-1 text-xs">
                                                @foreach ($step->details ?? [] as $i => $detail)
                                                    <li id="detail-item-{{ $step->no }}-{{ $i }}">
                                                        <span
                                                            id="detail-text-{{ $step->no }}-{{ $i }}">{{ $detail }}</span>
                                                        @can('update', $mwsPart)
                                                            <button
                                                                onclick="editDetail('{{ $mwsPart->id }}', {{ $step->no }}, {{ $i }})"
                                                                class="ml-1 text-blue-500 hover:text-blue-700 text-xs font-semibold">(Edit)</button>
                                                            <button
                                                                onclick="deleteDetail('{{ $mwsPart->id }}', {{ $step->no }}, {{ $i }})"
                                                                class="ml-1 text-red-500 hover:text-red-700 text-xs font-semibold">(Hapus)</button>
                                                        @endcan
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        {{-- Add Detail --}}
                                        @can('update', $mwsPart)
                                            <div class="mt-3 pt-3 border-t border-gray-100">
                                                <input type="text" id="new-detail-input-{{ $step->no }}"
                                                    class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-400"
                                                    placeholder="Tambah catatan baru...">
                                                <button onclick="addDetail('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="mt-1 px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded transition-colors">
                                                    Tambah Catatan
                                                </button>
                                            </div>
                                        @endcan
                                    </td>

                                    {{-- PLAN MAN --}}
                                    <td class="col-plan-man align-top">
                                        @can('update', $mwsPart)
                                            <div id="plan-man-view-{{ $step->no }}"
                                                class="flex items-center justify-between space-x-1">
                                                <span id="plan-man-text-{{ $step->no }}" class="text-sm text-gray-700">
                                                    {{ $step->plan_man ?? 'N/A' }}
                                                </span>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'man', true)"
                                                    class="p-1 text-blue-600 hover:text-blue-800 rounded"
                                                    title="Edit Plan Man">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                            </div>
                                            <div id="plan-man-edit-{{ $step->no }}" class="plan-edit-area">
                                                <input type="text" id="plan-man-input-{{ $step->no }}"
                                                    value="{{ $step->plan_man }}"
                                                    class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400"
                                                    placeholder="Contoh: 2">
                                                <button onclick="savePlan('{{ $mwsPart->id }}', {{ $step->no }}, 'man')"
                                                    class="mt-1 w-full px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded">
                                                    <i class="fas fa-save mr-1"></i> Simpan
                                                </button>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'man', false)"
                                                    class="mt-1 w-full px-2 py-1 bg-gray-400 hover:bg-gray-500 text-white text-xs rounded">
                                                    Batal
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-sm text-center text-gray-600">{{ $step->plan_man ?? 'N/A' }}</p>
                                        @endcan
                                    </td>

                                    {{-- PLAN HOURS --}}
                                    <td class="col-plan-hrs align-top">
                                        @can('update', $mwsPart)
                                            <div id="plan-hours-view-{{ $step->no }}"
                                                class="flex items-center justify-between space-x-1">
                                                <span id="plan-hours-text-{{ $step->no }}" class="text-sm text-gray-700">
                                                    {{ $step->plan_hours ?? 'N/A' }}
                                                </span>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'hours', true)"
                                                    class="p-1 text-blue-600 hover:text-blue-800 rounded"
                                                    title="Edit Plan Hours">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>
                                            </div>
                                            <div id="plan-hours-edit-{{ $step->no }}" class="plan-edit-area">
                                                <input type="text" id="plan-hours-input-{{ $step->no }}"
                                                    value="{{ $step->plan_hours }}"
                                                    class="w-full border rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400"
                                                    placeholder="Contoh: 8:00">
                                                <button
                                                    onclick="savePlan('{{ $mwsPart->id }}', {{ $step->no }}, 'hours')"
                                                    class="mt-1 w-full px-2 py-1 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded">
                                                    <i class="fas fa-save mr-1"></i> Simpan
                                                </button>
                                                <button onclick="togglePlanEdit({{ $step->no }}, 'hours', false)"
                                                    class="mt-1 w-full px-2 py-1 bg-gray-400 hover:bg-gray-500 text-white text-xs rounded">
                                                    Batal
                                                </button>
                                            </div>
                                        @else
                                            <p class="text-sm text-center text-gray-600">{{ $step->plan_hours ?? 'N/A' }}</p>
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
                                                            class="flex items-center justify-between bg-gray-200 px-2 py-1 rounded">
                                                            <span>{{ $mech->name }} - ({{ $mech->nik }})</span>
                                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                                                <button
                                                                    onclick="removeMechanicFromStep('{{ $mwsPart->id }}', {{ $step->no }}, '{{ $mech->nik }}')"
                                                                    class="ml-2 text-red-500 hover:text-red-700 font-bold"
                                                                    title="Hapus Mekanik">&times;</button>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-xs text-gray-500 italic">Belum ada mekanik.</p>
                                            @endif

                                            {{-- Admin: Assign Mekanik Dropdown --}}
                                            @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']) && !$techApproved)
                                                @if (count($mechanicNiks) < ($step->plan_man ?? 999))
                                                    <div class="mt-2">
                                                        <select id="assign-mechanic-select-{{ $step->no }}"
                                                            class="w-full border rounded px-2 py-1 text-xs mb-1">
                                                            <option value="">-- Pilih Mekanik --</option>
                                                            {{-- Ini perlu di-pass dari controller --}}
                                                            @foreach ($availableMechanics ?? [] as $mechanic)
                                                                <option value="{{ $mechanic->nik }}">
                                                                    {{ $mechanic->name }} ({{ $mechanic->nik }})</option>
                                                            @endforeach
                                                        </select>
                                                        <button
                                                            onclick="assignMechanicToStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="w-full px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-bold transition-colors">
                                                            <i class="fas fa-user-plus mr-1"></i> Assign
                                                        </button>
                                                    </div>
                                                @else
                                                    <p class="text-xs text-orange-500 mt-1">Slot mekanik penuh
                                                        ({{ $step->plan_man }}).</p>
                                                @endif
                                            @endif

                                            {{-- Mekanik: Sign On Sendiri --}}
                                            @if ($isMechanic && !$userInStep && !$techApproved)
                                                <button onclick="addMeToStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="w-full mt-1 px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-xs font-bold transition-colors {{ $isMwsLocked || $planIncomplete || count($mechanicNiks) >= ($step->plan_man ?? 999) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    @if ($isMwsLocked) disabled title="MWS terkunci."
                                                    @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu."
                                                    @elseif(count($mechanicNiks) >= ($step->plan_man ?? 999)) disabled title="Slot mekanik sudah penuh." @endif>
                                                    <i class="fas fa-sign-in-alt mr-1"></i> Sign On
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- ACTUAL HOURS --}}
                                    <td class="col-act-hrs align-top">
                                        <div class="flex flex-col items-center space-y-1">
                                            <input type="hidden" id="hours-{{ $step->no }}"
                                                value="{{ $step->hours ?? '00:00' }}">
                                            <span id="hours-display-{{ $step->no }}"
                                                class="font-mono text-lg font-semibold text-gray-700"
                                                @if ($timerRunning) data-start-time="{{ $step->timer_start_time }}"
                                          data-initial-hours="{{ $step->hours ?? '00:00' }}" @endif>
                                                {{ $step->hours ?? '00:00' }}
                                            </span>

                                            @if ($isMechanic && $userInStep && !$techApproved)
                                                @if ($timerRunning)
                                                    <button
                                                        onclick="stopTimer('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="w-full px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors
                                                   {{ $isMwsLocked ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                        @if ($isMwsLocked) disabled @endif>
                                                        <i class="fas fa-stop mr-1"></i> Stop
                                                    </button>
                                                @else
                                                    <button
                                                        onclick="startTimer('{{ $mwsPart->id }}', {{ $step->no }})"
                                                        class="w-full px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs transition-colors
                                                   {{ $isMwsLocked || $planIncomplete ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                        @if ($isMwsLocked) disabled title="MWS terkunci"
                                            @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu." @endif>
                                                        <i class="fas fa-play mr-1"></i> Start
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    {{-- TECH --}}
                                    <td class="col-tech align-top tech-cell">
                                        @if ($isMechanic && $userInStep && !$techApproved)
                                            <div class="flex items-center justify-center min-h-[40px]">
                                                <button
                                                    onclick="approveStep('{{ $mwsPart->id }}', {{ $step->no }})"
                                                    class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm font-medium transition-colors
                                                   {{ $isMwsLocked || $timerRunning || $planIncomplete ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                    @if ($isMwsLocked) disabled title="MWS terkunci."
                                            @elseif($timerRunning) disabled title="Hentikan timer terlebih dahulu."
                                            @elseif($planIncomplete) disabled title="PLAN MAN dan PLAN HOURS harus diisi dulu." @endif>
                                                    Approve
                                                </button>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center min-h-[40px] space-y-1">
                                                <span class="text-sm font-semibold text-gray-900 text-center">
                                                    {{ $techApproved ? 'Approved' : 'N/A' }}
                                                </span>
                                                @if ($techApproved)
                                                    @can('update', $mwsPart)
                                                        <button
                                                            onclick="cancelApproval('{{ $mwsPart->id }}', {{ $step->no }})"
                                                            class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Tanggal & Durasi --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tanggal &amp; Durasi Pengerjaan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="text" readonly
                                value="{{ $mwsPart->start_date ? \Carbon\Carbon::parse($mwsPart->start_date)->format('d/m/Y') : '' }}"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Finish Date</label>
                            <input type="text" readonly
                                value="{{ $mwsPart->finish_date ? \Carbon\Carbon::parse($mwsPart->finish_date)->format('d/m/Y') : '' }}"
                                placeholder="dd/mm/yyyy"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 text-gray-700 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Durasi Pengerjaan</label>
                            <p class="text-sm font-semibold text-gray-800" id="total-duration">
                                {{ $mwsPart->total_duration ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Approved MWS --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Approved Maintenance Work Sheet</h3>
                    <div class="space-y-4">

                        {{-- Prepared By --}}
                        <div
                            class="p-4 rounded-lg border transition-colors duration-300 {{ $mwsPart->preparedBy ? 'border-green-400 bg-green-50 shadow-inner' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">Prepared By</h4>
                                    @if ($mwsPart->preparedBy)
                                        <div class="flex items-center mt-1">
                                            <i class="fas fa-user-check text-green-600 mr-2"></i>
                                            <p class="text-sm text-gray-700 font-medium">{{ $mwsPart->preparedBy }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 ml-6">
                                            {{ $mwsPart->preparedAt ? \Carbon\Carbon::parse($mwsPart->preparedAt)->format('d/m/Y H:i') : '' }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500 italic mt-1">Menunggu Approved...</p>
                                    @endif
                                </div>
                                @if (!$mwsPart->preparedBy && in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                    <button onclick="signDocument('{{ $mwsPart->id }}', 'prepared')"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium shadow hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                                        <i class="fas fa-signature mr-1"></i> Sign
                                    </button>
                                @elseif($mwsPart->preparedBy)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                        @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                            <button
                                                onclick="cancelSignature('{{ $mwsPart->id }}', 'prepared', 'Anda yakin ingin membatalkan tanda tangan Prepared By?')"
                                                class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Batal</button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Approved By --}}
                        <div
                            class="p-4 rounded-lg border transition-colors duration-300 {{ $mwsPart->approvedBy ? 'border-green-400 bg-green-50 shadow-inner' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">Approved By</h4>
                                    @if ($mwsPart->approvedBy)
                                        <div class="flex items-center mt-1">
                                            <i class="fas fa-user-check text-green-600 mr-2"></i>
                                            <p class="text-sm text-gray-700 font-medium">{{ $mwsPart->approvedBy }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 ml-6">
                                            {{ $mwsPart->approvedAt ? \Carbon\Carbon::parse($mwsPart->approvedAt)->format('d/m/Y H:i') : '' }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500 italic mt-1">Menunggu Approved...</p>
                                    @endif
                                </div>
                                @if (!$mwsPart->approvedBy && in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                    <button onclick="signDocument('{{ $mwsPart->id }}', 'approved')"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                                        <i class="fas fa-signature mr-1"></i> Sign
                                    </button>
                                @elseif($mwsPart->approvedBy)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                        @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                            <button
                                                onclick="cancelSignature('{{ $mwsPart->id }}', 'approved', 'Anda yakin ingin membatalkan tanda tangan Approved By?')"
                                                class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Batal</button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Verified By --}}
                        <div
                            class="p-4 rounded-lg border transition-colors duration-300 {{ $mwsPart->verifiedBy ? 'border-green-400 bg-green-50 shadow-inner' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-800">Verified By</h4>
                                    @if ($mwsPart->verifiedBy)
                                        <div class="flex items-center mt-1">
                                            <i class="fas fa-user-check text-green-600 mr-2"></i>
                                            <p class="text-sm text-gray-700 font-medium">{{ $mwsPart->verifiedBy }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 ml-6">
                                            {{ $mwsPart->verifiedAt ? \Carbon\Carbon::parse($mwsPart->verifiedAt)->format('d/m/Y H:i') : '' }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500 italic mt-1">Menunggu Approved Quality...</p>
                                    @endif
                                </div>
                                @if (!$mwsPart->verifiedBy && (auth()->user()->role ?? '') === 'quality2')
                                    <button onclick="signDocument('{{ $mwsPart->id }}', 'verified')"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                                        <i class="fas fa-signature mr-1"></i> Sign
                                    </button>
                                @elseif($mwsPart->verifiedBy)
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                        @if (in_array(auth()->user()->role ?? '', ['admin', 'superadmin']))
                                            <button
                                                onclick="cancelSignature('{{ $mwsPart->id }}', 'verified', 'Anda yakin ingin membatalkan tanda tangan Verified By?')"
                                                class="px-3 py-1 bg-red-500 text-white rounded text-xs hover:bg-red-600 transition-colors">Batal</button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>{{-- end main container --}}
    </div>{{-- end min-h-screen --}}

    {{-- ==================== PASS DATA TO JS ==================== --}}
    <script>
        const currentUserRole = @json(auth()->user()->getRoleNames()->first() ?? '');
        const currentUserNik = @json(auth()->user()->nik ?? '');
        const partId = @json($mwsPart->id);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const isMwsLocked = @json($isMwsLocked);
    </script>
    <script src="{{ asset('js/mws_detail_logic.js') }}" defer></script>

    {{-- ==================== INLINE SCRIPT ==================== --}}
    @push('scripts')
        <script>
            /* ─── UI Helpers ─── */
            function showToast(message, type = 'success') {
                const el = document.getElementById('toast-notification');
                const msg = document.getElementById('toast-message');
                const icon = document.getElementById('toast-icon');
                el.className = type;
                el.style.display = 'block';
                msg.textContent = message;
                icon.className = type === 'success' ?
                    'fas fa-check-circle text-xl mt-0.5' :
                    type === 'error' ?
                    'fas fa-times-circle text-xl mt-0.5' :
                    'fas fa-info-circle text-xl mt-0.5';
                setTimeout(() => dismissToast(), 4000);
            }

            function dismissToast() {
                document.getElementById('toast-notification').style.display = 'none';
            }

            /* ─── Section Toggle ─── */
            function toggleSection(id) {
                const el = document.getElementById(id);
                el.classList.toggle('hidden');
            }

            /* ─── Edit MWS Info ─── */
            function toggleEditMwsInfo(show) {
                document.getElementById('mws-info-view').classList.toggle('hidden', show);
                document.getElementById('mws-info-edit').classList.toggle('hidden', !show);
            }
            async function saveMwsInfo(e) {
                e.preventDefault();
                const form = e.target;
                const data = Object.fromEntries(new FormData(form));
                try {
                    const res = await fetch(`/mws/${partId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify(data)
                    });
                    if (res.ok) {
                        showToast('MWS Info berhasil disimpan!');
                        location.reload();
                    } else showToast('Gagal menyimpan.', 'error');
                } catch {
                    showToast('Terjadi kesalahan.', 'error');
                }
            }

            /* ─── Plan Edit Toggle ─── */
            function togglePlanEdit(stepNo, field, show) {
                document.getElementById(`plan-${field}-view-${stepNo}`).style.display = show ? 'none' : 'flex';
                document.getElementById(`plan-${field}-edit-${stepNo}`).style.display = show ? 'block' : 'none';
            }

            /* ─── Select All ─── */
            document.addEventListener('DOMContentLoaded', () => {
                const selectAll = document.getElementById('select-all-steps');
                if (selectAll) {
                    selectAll.addEventListener('change', () => {
                        document.querySelectorAll('.step-checkbox').forEach(cb => cb.checked = selectAll
                            .checked);
                        updateSmartDeleteBtn();
                    });
                    document.querySelectorAll('.step-checkbox').forEach(cb => {
                        cb.addEventListener('change', updateSmartDeleteBtn);
                    });
                }

                // Auto-start live timers
                document.querySelectorAll('[data-start-time]').forEach(el => {
                    startLiveTimer(el);
                });
            });

            function updateSmartDeleteBtn() {
                const checked = document.querySelectorAll('.step-checkbox:checked').length;
                const btn = document.getElementById('smart-delete-btn');
                if (btn) btn.classList.toggle('hidden', checked === 0);
            }

            /* ─── Live Timer ─── */
            function startLiveTimer(el) {
                const startTime = new Date(el.dataset.startTime);
                const initialParts = (el.dataset.initialHours || '00:00').split(':').map(Number);
                const initialSecs = initialParts[0] * 3600 + (initialParts[1] || 0) * 60;

                setInterval(() => {
                    const elapsed = Math.floor((Date.now() - startTime) / 1000) + initialSecs;
                    const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                    const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                    el.textContent = `${h}:${m}`;
                }, 1000);
            }

            /* ─── File name display ─── */
            function updateFileName(input, stepNo) {
                const display = document.getElementById(`file-name-display-${stepNo}`);
                if (display) {
                    display.textContent = input.files.length > 0 ? [...input.files].map(f => f.name).join(', ') :
                        'Pilih file...';
                }
            }

            function updateMwsFileName(input) {
                const display = document.getElementById('mws-file-name-display');
                if (display) {
                    display.textContent = input.files.length > 0 ? [...input.files].map(f => f.name).join(', ') :
                        'Pilih file lampiran...';
                }
            }

            /* ─── Final Inspection ─── */
            function enableFinalApprove(stepNo) {
                const sel = document.getElementById(`status-s-us-select-${stepNo}`);
                const btn = document.getElementById(`final-approve-btn-${stepNo}`);
                if (sel.value) {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            /* ─── Stripping Notification ─── */
            function dismissStrippingNotification() {
                document.getElementById('stripping-notification').style.display = 'none';
            }

            /* ─── Duplicate Confirm ─── */
            function confirmDuplicateMws(id) {
                if (confirm('Yakin ingin menduplikasi MWS ini?')) {
                    fetch(`/mws/${id}/duplicate`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }).then(r => r.json()).then(d => {
                        if (d.redirect) window.location.href = d.redirect;
                        else showToast(d.message || 'Berhasil diduplikasi!');
                    }).catch(() => showToast('Gagal menduplikasi.', 'error'));
                }
            }
        </script>
    @endpush
@endsection
