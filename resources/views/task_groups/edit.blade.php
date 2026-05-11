@extends('layouts.app')

@section('content')
<div class="container-lg py-5">

    {{-- Breadcrumb --}}
    <div class="mb-4">
        <div class="d-flex align-items-center gap-2 small text-muted mb-1">
            <a href="{{ route('projects.index') }}" class="text-muted text-decoration-none">Projects</a>
            <span>/</span>
            <a href="{{ route('projects.show', $dockPhase->project_id) }}" class="text-muted text-decoration-none">
                {{ $dockPhase->project->aircraft_reg ?? 'Project' }}
            </a>
            <span>/</span>
            <span class="text-dark">Edit Task Group</span>
        </div>
        <h3 class="fw-bold text-dark mb-1">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Task Group
        </h3>
    </div>

    <form action="{{ route('task-groups.update', [$dockPhase, $taskGroup]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="text-white mb-0 fw-semibold">
                    <i class="fas fa-layer-group me-2"></i>Informasi Task Group
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">

                    <div class="col-12 col-md-3">
                        <label for="no" class="form-label fw-semibold text-dark mb-2">Nomor</label>
                        <input type="text" id="no" name="no"
                            value="{{ old('no', $taskGroup->no) }}"
                            placeholder="Contoh: B.1"
                            class="form-control form-control-lg @error('no') is-invalid @enderror" />
                        @error('no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-9">
                        <label for="name" class="form-label fw-semibold text-dark mb-2">
                            Nama Task Group <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $taskGroup->name) }}"
                            required
                            class="form-control form-control-lg @error('name') is-invalid @enderror" />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="start_date" class="form-label fw-semibold text-dark mb-2">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date"
                            value="{{ old('start_date', $taskGroup->start_date?->format('Y-m-d')) }}"
                            class="form-control form-control-lg" />
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="finish_date" class="form-label fw-semibold text-dark mb-2">Tanggal Selesai</label>
                        <input type="date" id="finish_date" name="finish_date"
                            value="{{ old('finish_date', $taskGroup->finish_date?->format('Y-m-d')) }}"
                            class="form-control form-control-lg" />
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="work_days" class="form-label fw-semibold text-dark mb-2">Work Days</label>
                        <input type="number" id="work_days" name="work_days"
                            value="{{ old('work_days', $taskGroup->work_days) }}"
                            placeholder="0" min="1"
                            class="form-control form-control-lg" />
                    </div>

                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="{{ route('projects.show', $dockPhase->project_id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-2"></i>Update Task Group
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
</style>
@endsection