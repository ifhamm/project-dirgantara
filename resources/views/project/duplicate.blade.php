@extends('layouts.app')

@section('content')
<div class="container-lg py-5">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex align-items-center gap-2 mb-2">
                <a href="{{ route('projects.show', $project) }}" class="text-muted text-decoration-none small">
                    <i class="fas fa-folder me-1"></i>{{ $project->aircraft_reg }}
                </a>
                <span class="text-muted small">/</span>
                <span class="small text-dark fw-semibold">Duplicate</span>
            </div>
            <h3 class="fw-bold text-dark">
                <i class="fas fa-copy me-2 text-primary"></i>Duplicate Project
            </h3>
            <p class="text-muted small mt-1">Buat salinan project dengan data yang dapat disesuaikan</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-8">

            <form action="{{ route('projects.store-duplicate', $project) }}" method="POST">
                @csrf

                {{-- ===== INFORMASI PROJECT ===== --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="text-white mb-0 fw-semibold">
                            <i class="fas fa-project-diagram me-2"></i>Informasi Project (Dapat Diubah)
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

                {{-- ===== SUMBER PROJECT ===== --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-copy me-2 text-muted"></i>Data yang Akan Disalin Otomatis
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #f8f9fa; border-left: 4px solid #667eea;">
                                    <i class="fas fa-check-circle fa-lg text-success"></i>
                                    <div>
                                        <p class="mb-0 fw-semibold text-dark small">Project Structure</p>
                                        <p class="mb-0 text-muted small">Semua Dock Phases, Task Groups, dan Tasks akan disalin</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #f8f9fa; border-left: 4px solid #667eea;">
                                    <i class="fas fa-check-circle fa-lg text-success"></i>
                                    <div>
                                        <p class="mb-0 fw-semibold text-dark small">Timeline & Allocation</p>
                                        <p class="mb-0 text-muted small">Tanggal, working days, dan persentase alokasi dari project asli</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-3 p-3 rounded" style="background: #fff5e6; border-left: 4px solid #f5576c;">
                                    <i class="fas fa-times-circle fa-lg text-danger"></i>
                                    <div>
                                        <p class="mb-0 fw-semibold text-dark small">MWS (Maintenance Work Sheet)</p>
                                        <p class="mb-0 text-muted small">MWS tidak akan disalin karena setiap project memiliki MWS yang unik</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== SOURCE INFORMATION ===== --}}
                <div class="card shadow-sm border-0 mb-4" style="background: #f0f0ff; border-left: 4px solid #667eea;">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold text-dark mb-3">
                            <i class="fas fa-plane me-2"></i>Project Sumber
                        </h6>
                        <div class="row g-2 small">
                            <div class="col-12 col-md-6">
                                <p class="mb-1 text-muted">Aircraft Type:</p>
                                <p class="fw-semibold text-dark">{{ $project->aircraft_type }}</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="mb-1 text-muted">Aircraft Reg:</p>
                                <p class="fw-semibold text-dark">{{ $project->aircraft_reg }}</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="mb-1 text-muted">Total Work Days:</p>
                                <p class="fw-semibold text-dark">{{ $project->work_days ?? '—' }} hari</p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="mb-1 text-muted">Timeline:</p>
                                <p class="fw-semibold text-dark">
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d M Y') : '—' }}
                                    —
                                    {{ $project->finish_date ? \Carbon\Carbon::parse($project->finish_date)->format('d M Y') : '—' }}
                                </p>
                            </div>
                            <div class="col-12">
                                <p class="mb-1 text-muted">Task Groups / Tasks:</p>
                                <p class="fw-semibold text-dark">
                                    {{ $project->dockPhases->sum(fn($p) => $p->taskGroups->count()) }} task groups,
                                    {{ $project->dockPhases->sum(fn($p) => $p->taskGroups->sum(fn($tg) => $tg->tasks->count())) }} tasks
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Duplicate Project
                    </button>
                </div>

            </form>

        </div>

        {{-- INFO SIDEBAR --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 2rem;">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                    <h6 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-lightbulb me-2"></i>Tips
                    </h6>
                </div>
                <div class="card-body p-4 small">
                    <p class="mb-3">
                        <strong>Mengapa Duplicate?</strong>
                        Gunakan fitur ini ketika Anda ingin membuat project baru dengan struktur yang sama
                        seperti project sebelumnya, namun dengan aircraft atau customer yang berbeda.
                    </p>
                    <p class="mb-3">
                        <strong>Yang Berubah:</strong>
                        Field yang Anda ubah di form akan otomatis menimpa data asli.
                        Field yang tidak berubah akan tetap sama dengan project sumber.
                    </p>
                    <p class="mb-0">
                        <strong>Catatan:</strong>
                        Project hasil duplicate bersifat independent. Perubahan pada project sumber
                        tidak akan mempengaruhi project hasil duplicate.
                    </p>
                </div>
            </div>
        </div>

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
</style>

@endsection
