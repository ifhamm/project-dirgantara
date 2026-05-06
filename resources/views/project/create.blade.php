@extends('layouts.app')

@section('content')
<div class="container-lg py-5">
    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-folder-plus me-2 text-primary"></i>Buat Project Baru
            </h3>
            <p class="text-muted small mt-1">Import Gantt Chart Excel atau isi form manual untuk membuat project baru</p>
        </div>
    </div>

    {{-- ==================== TAB SWITCH ==================== --}}
    <ul class="nav nav-pills mb-4" id="createTab">
        <li class="nav-item me-2">
            <button class="nav-link active px-4" id="tab-import" onclick="switchTab('import')">
                <i class="fas fa-file-excel me-2"></i>Import dari Excel
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4" id="tab-manual" onclick="switchTab('manual')">
                <i class="fas fa-pencil-alt me-2"></i>Input Manual
            </button>
        </li>
    </ul>

    {{-- ==================== PANEL: IMPORT EXCEL ==================== --}}
    <div id="panel-import">

        <form action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-file-excel me-2"></i>Upload Gantt Chart Excel
                    </h5>
                </div>
                <div class="card-body p-4">

                    {{-- Drop Zone --}}
                    <div class="drop-zone mb-4" id="dropZone">
                        <div class="drop-zone-inner">
                            <i class="fas fa-cloud-upload-alt fa-3x text-success mb-3"></i>
                            <p class="fw-semibold text-dark mb-1">Drag & drop file Excel di sini</p>
                            <p class="text-muted small mb-3">atau klik tombol di bawah untuk pilih file</p>
                            <label for="ganttFile" class="btn btn-outline-success btn-sm px-4" style="cursor:pointer;">
                                <i class="fas fa-folder-open me-1"></i> Pilih File
                            </label>
                        </div>
                        <input
                            type="file"
                            name="file"
                            id="ganttFile"
                            accept=".xlsx,.xls"
                            style="display:none;"
                        >
                    </div>

                    @error('file')
                        <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror

                    {{-- Preview setelah pilih file --}}
                    <div id="filePreview" style="display:none;" class="mt-3">
                        <div class="d-flex align-items-center gap-3 p-3 rounded border bg-light">
                            <i class="fas fa-file-excel fa-2x text-success"></i>
                            <div>
                                <p class="fw-semibold mb-0 small" id="previewName">—</p>
                                <p class="text-muted mb-0" style="font-size: 0.78rem;" id="previewSize">—</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="clearFile()">
                                <i class="fas fa-times"></i> Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="alert alert-info border-0 d-flex align-items-start gap-3 p-3 mt-3" style="background: #e8f4fd;">
                        <i class="fas fa-info-circle text-primary mt-1"></i>
                        <div class="small">
                            <strong>Format yang didukung:</strong> Gantt Chart dengan sheet <code>Reporting</code>,
                            kolom <code>ROW LEVEL</code> berisi Level 1–5, dan kolom <code>NO</code> berisi
                            <strong>A</strong> (Pre Dock), <strong>B</strong> (In Dock), <strong>C</strong> (Post Dock).
                            Sistem akan otomatis mengklasifikasi dan membuat hierarki project.
                        </div>
                    </div>

                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-magic me-2"></i>Import & Klasifikasi Otomatis
                </button>
            </div>

        </form>

    </div>

    {{-- ==================== PANEL: MANUAL ==================== --}}
    <div id="panel-manual" style="display:none;">

        <form action="{{ route('projects.store') }}" method="POST">
            @csrf

            {{-- ===== INFORMASI PROJECT ===== --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-project-diagram me-2"></i>Informasi Project
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">

                        <div class="col-12 col-md-6">
                            <label for="customer" class="form-label fw-semibold text-dark mb-2">
                                Customer <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="customer" name="customer"
                                value="{{ old('customer') }}"
                                placeholder="Contoh: UAE" required
                                class="form-control form-control-lg @error('customer') is-invalid @enderror" />
                            @error('customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="contract_no" class="form-label fw-semibold text-dark mb-2">No. Kontrak</label>
                            <input type="text" id="contract_no" name="contract_no"
                                value="{{ old('contract_no') }}"
                                placeholder="Contoh: AMU/MOD/2024/1625"
                                class="form-control form-control-lg @error('contract_no') is-invalid @enderror" />
                            @error('contract_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="aircraft_type" class="form-label fw-semibold text-dark mb-2">
                                Aircraft Type <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="aircraft_type" name="aircraft_type"
                                value="{{ old('aircraft_type') }}"
                                placeholder="Contoh: CN235-110" required
                                class="form-control form-control-lg @error('aircraft_type') is-invalid @enderror" />
                            @error('aircraft_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="aircraft_reg" class="form-label fw-semibold text-dark mb-2">
                                Aircraft Reg / S/N <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="aircraft_reg" name="aircraft_reg"
                                value="{{ old('aircraft_reg') }}"
                                placeholder="Contoh: TN 811" required
                                class="form-control form-control-lg @error('aircraft_reg') is-invalid @enderror" />
                            @error('aircraft_reg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold text-dark mb-2">Deskripsi Project</label>
                            <textarea id="description" name="description"
                                placeholder="Contoh: PERIODICAL INSPECTION, REWIRING, UPGRADE SYSTEM CN235-110..."
                                class="form-control form-control-lg @error('description') is-invalid @enderror"
                                rows="3" style="resize: vertical;">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- ===== TIMELINE PROJECT ===== --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-calendar-alt me-2"></i>Timeline Project
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-12 col-md-4">
                            <label for="start_date" class="form-label fw-semibold text-dark mb-2">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date"
                                value="{{ old('start_date') }}"
                                class="form-control form-control-lg" />
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="finish_date" class="form-label fw-semibold text-dark mb-2">Tanggal Selesai</label>
                            <input type="date" id="finish_date" name="finish_date"
                                value="{{ old('finish_date') }}"
                                class="form-control form-control-lg" />
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="work_days" class="form-label fw-semibold text-dark mb-2">Work Days</label>
                            <input type="number" id="work_days" name="work_days"
                                value="{{ old('work_days') }}"
                                placeholder="Contoh: 313" min="1"
                                class="form-control form-control-lg" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== DOCK PHASES ===== --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-layer-group me-2"></i>Dock Phases
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        @foreach ([
                            ['key' => 'predock',  'label' => 'Pre Dock',  'icon' => 'fas fa-sign-in-alt',  'color' => '#667eea'],
                            ['key' => 'indock',   'label' => 'In Dock',   'icon' => 'fas fa-tools',         'color' => '#f5576c'],
                            ['key' => 'postdock', 'label' => 'Post Dock', 'icon' => 'fas fa-sign-out-alt',  'color' => '#11998e'],
                        ] as $phase)
                        <div class="col-12">
                            <div class="p-3 rounded-3 border" style="border-color: {{ $phase['color'] }}30 !important; background: {{ $phase['color'] }}08;">
                                <div class="mb-3">
                                    <span class="badge fw-semibold px-3 py-2" style="background: {{ $phase['color'] }}20; color: {{ $phase['color'] }}; font-size: 0.82rem;">
                                        <i class="{{ $phase['icon'] }} me-1"></i>{{ $phase['label'] }}
                                    </span>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold text-dark mb-1 small">Tanggal Mulai</label>
                                        <input type="date" name="phases[{{ $phase['key'] }}][start_date]"
                                            value="{{ old('phases.' . $phase['key'] . '.start_date') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold text-dark mb-1 small">Tanggal Selesai</label>
                                        <input type="date" name="phases[{{ $phase['key'] }}][finish_date]"
                                            value="{{ old('phases.' . $phase['key'] . '.finish_date') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-semibold text-dark mb-1 small">Work Days</label>
                                        <input type="number" name="phases[{{ $phase['key'] }}][work_days]"
                                            value="{{ old('phases.' . $phase['key'] . '.work_days') }}"
                                            placeholder="0" min="0" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Buat Project
                </button>
            </div>

        </form>

    </div>

</div>

<style>
    .form-control-lg {
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.5rem;
    }
    .form-control-lg:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-label { color: #2c3e50; }
    .text-danger { font-weight: 600; }

    .nav-pills .nav-link {
        color: #6c757d;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: transparent;
        color: #fff;
    }

    .drop-zone {
        border: 2px dashed #38ef7d;
        border-radius: 1rem;
        background: #f0fdf4;
        transition: all 0.2s;
        min-height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .drop-zone:hover, .drop-zone.dragover {
        border-color: #11998e;
        background: #e6faf5;
    }
    .drop-zone-inner {
        text-align: center;
        padding: 2rem;
    }
</style>

<script>
    // ── Tab switch (tanpa localStorage) ──────────────
    function switchTab(tab) {
        document.getElementById('panel-import').style.display = tab === 'import' ? 'block' : 'none';
        document.getElementById('panel-manual').style.display = tab === 'manual'  ? 'block' : 'none';
        document.getElementById('tab-import').classList.toggle('active', tab === 'import');
        document.getElementById('tab-manual').classList.toggle('active', tab === 'manual');
    }

    // ── File input ────────────────────────────────────
    const fileInput   = document.getElementById('ganttFile');
    const filePreview = document.getElementById('filePreview');
    const previewName = document.getElementById('previewName');
    const previewSize = document.getElementById('previewSize');
    const dropZone    = document.getElementById('dropZone');

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) handleFile(this.files[0]);
    });

    function handleFile(file) {
        previewName.textContent = file.name;
        previewSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
        filePreview.style.display = 'block';
    }

    function clearFile() {
        fileInput.value = '';
        filePreview.style.display = 'none';
    }

    // ── Drag & Drop ───────────────────────────────────
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', function () {
        this.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (!file) return;
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        handleFile(file);
    });
</script>

@endsection