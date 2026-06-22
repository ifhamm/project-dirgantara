@extends('layouts.app')

@section('content')
<div class="container-lg py-5">
    <div class="row mb-4">
        <div class="col">
            <h3 class="fw-bold text-dark">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Project: {{ $project->aircraft_reg }}
            </h3>
            <p class="text-muted small mt-1">Ubah informasi detail project dan timeline dock phases di bawah ini</p>
        </div>
    </div>

    @php
        $predock = $project->dockPhases->firstWhere('type', 'predock');
        $indock = $project->dockPhases->firstWhere('type', 'indock');
        $postdock = $project->dockPhases->firstWhere('type', 'postdock');
    @endphp

    <form action="{{ route('projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')

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
                            value="{{ old('customer', $project->customer) }}"
                            placeholder="Contoh: UAE" required
                            class="form-control form-control-lg @error('customer') is-invalid @enderror" />
                        @error('customer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="contract_no" class="form-label fw-semibold text-dark mb-2">No. Kontrak</label>
                        <input type="text" id="contract_no" name="contract_no"
                            value="{{ old('contract_no', $project->contract_no) }}"
                            placeholder="Contoh: AMU/MOD/2024/1625"
                            class="form-control form-control-lg @error('contract_no') is-invalid @enderror" />
                        @error('contract_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="aircraft_type" class="form-label fw-semibold text-dark mb-2">
                            Aircraft Type <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="aircraft_type" name="aircraft_type"
                            value="{{ old('aircraft_type', $project->aircraft_type) }}"
                            placeholder="Contoh: CN235-110" required
                            class="form-control form-control-lg @error('aircraft_type') is-invalid @enderror" />
                        @error('aircraft_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="aircraft_series" class="form-label fw-semibold text-dark mb-2">
                            Aircraft Series
                        </label>
                        <input type="text" id="aircraft_series" name="aircraft_series"
                            value="{{ old('aircraft_series', $project->aircraft_series) }}"
                            placeholder="Contoh: Series 300"
                            class="form-control form-control-lg @error('aircraft_series') is-invalid @enderror" />
                        @error('aircraft_series')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="aircraft_reg" class="form-label fw-semibold text-dark mb-2">
                            Aircraft Reg / S/N <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="aircraft_reg" name="aircraft_reg"
                            value="{{ old('aircraft_reg', $project->aircraft_reg) }}"
                            placeholder="Contoh: TN 811" required
                            class="form-control form-control-lg @error('aircraft_reg') is-invalid @enderror" />
                        @error('aircraft_reg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold text-dark mb-2">Deskripsi Project</label>
                        <textarea id="description" name="description"
                            placeholder="Contoh: PERIODICAL INSPECTION, REWIRING, UPGRADE SYSTEM CN235-110..."
                            class="form-control form-control-lg @error('description') is-invalid @enderror"
                            rows="3" style="resize: vertical;">{{ old('description', $project->description) }}</textarea>
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
                            value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                            class="form-control form-control-lg @error('start_date') is-invalid @enderror" />
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="finish_date" class="form-label fw-semibold text-dark mb-2">Tanggal Selesai</label>
                        <input type="date" id="finish_date" name="finish_date"
                            value="{{ old('finish_date', $project->finish_date?->format('Y-m-d')) }}"
                            class="form-control form-control-lg @error('finish_date') is-invalid @enderror" />
                        @error('finish_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="work_days" class="form-label fw-semibold text-dark mb-2">Work Days</label>
                        <input type="number" id="work_days" name="work_days"
                            value="{{ old('work_days', $project->work_days) }}"
                            placeholder="Contoh: 313" min="1"
                            class="form-control form-control-lg @error('work_days') is-invalid @enderror" />
                        @error('work_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        ['key' => 'predock',  'label' => 'Pre Dock',  'icon' => 'fas fa-sign-in-alt',  'color' => '#667eea', 'instance' => $predock],
                        ['key' => 'indock',   'label' => 'In Dock',   'icon' => 'fas fa-tools',         'color' => '#f5576c', 'instance' => $indock],
                        ['key' => 'postdock', 'label' => 'Post Dock', 'icon' => 'fas fa-sign-out-alt',  'color' => '#11998e', 'instance' => $postdock],
                    ] as $phase)
                    <div class="col-12">
                        <div class="p-3 rounded-3 border" style="border-color: {{ $phase['color'] }}30 !important; background: {{ $phase['color'] }}08;">
                            <div class="mb-3">
                                <span class="badge fw-semibold px-3 py-2" style="background: {{ $phase['color'] }}20; color: {{ $phase['color'] }}; font-size: 0.82rem;">
                                    <i class="{{ $phase['icon'] }} me-1"></i>{{ $phase['label'] }}
                                </span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold text-dark mb-1 small">Tanggal Mulai</label>
                                    <input type="date" name="phases[{{ $phase['key'] }}][start_date]"
                                        value="{{ old('phases.' . $phase['key'] . '.start_date', $phase['instance']?->start_date?->format('Y-m-d')) }}"
                                        class="form-control @error('phases.' . $phase['key'] . '.start_date') is-invalid @enderror">
                                    @error('phases.' . $phase['key'] . '.start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold text-dark mb-1 small">Tanggal Selesai</label>
                                    <input type="date" name="phases[{{ $phase['key'] }}][finish_date]"
                                        value="{{ old('phases.' . $phase['key'] . '.finish_date', $phase['instance']?->finish_date?->format('Y-m-d')) }}"
                                        class="form-control @error('phases.' . $phase['key'] . '.finish_date') is-invalid @enderror">
                                    @error('phases.' . $phase['key'] . '.finish_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold text-dark mb-1 small">Work Days</label>
                                    <input type="number" name="phases[{{ $phase['key'] }}][work_days]"
                                        value="{{ old('phases.' . $phase['key'] . '.work_days', $phase['instance']?->work_days) }}"
                                        placeholder="0" min="0" class="form-control @error('phases.' . $phase['key'] . '.work_days') is-invalid @enderror">
                                    @error('phases.' . $phase['key'] . '.work_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold text-dark mb-1 small">Alokasi %</label>
                                    <input type="number" step="0.01" name="phases[{{ $phase['key'] }}][allocation_percentage]"
                                        value="{{ old('phases.' . $phase['key'] . '.allocation_percentage', $phase['instance']?->allocation_percentage) }}"
                                        placeholder="0" min="0" max="100" class="form-control @error('phases.' . $phase['key'] . '.allocation_percentage') is-invalid @enderror">
                                    @error('phases.' . $phase['key'] . '.allocation_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>Simpan Perubahan
            </button>
        </div>

    </form>
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
</style>
@endsection
