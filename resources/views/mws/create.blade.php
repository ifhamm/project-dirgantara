@extends('layouts.app')

@section('content')
    <div class="container-lg py-5">
        <div class="row mb-4">
            <div class="col">
                <h3 class="fw-bold text-dark">
                    <i class="fas fa-plus-circle me-2 text-primary"></i>Buat MWS Baru
                </h3>
                <p class="text-muted small mt-1">Isi form di bawah untuk membuat Maintenance Work Sheet baru</p>
            </div>
        </div>

        <form action="{{ route('mws.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @if (request('task_id'))
                <input type="hidden" name="task_id" value="{{ request('task_id') }}">
            @endif

            {{-- ==================== DETAIL PEKERJAAN & KOMPONEN ==================== --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-tools me-2"></i>Detail Pekerjaan & Komponen
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">

                        {{-- ROW 1 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Ref Logistic / PPC</label>
                            <input type="text" name="ref_logistic_ppc" placeholder="Boleh dikosongkan"
                                class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Customer <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="customer_name" placeholder="Contoh: Garuda Indonesia" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 2 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">WBS No. <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="wbs_no" placeholder="Contoh: A/590-0250N235-00-99-99" required
                                class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Part Name / Title <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" placeholder="Contoh: Angle of Attack Indicator" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 3 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Part Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="part_number" placeholder="Contoh: ADA-001" required
                                class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Serial Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="serial_number" placeholder="Contoh: SN123456" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 4 --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark mb-2">MDR Doc Defect</label>
                            <input type="text" name="mdr_doc_defect" placeholder="Boleh dikosongkan"
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 5 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Capability</label>
                            <input type="text" name="capability" placeholder="Boleh dikosongkan"
                                class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Shop Area <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="shop_area" placeholder="Contoh: IN" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 6 --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark mb-2">Remark MWS</label>
                            <textarea name="remark_mws" placeholder="Catatan atau keterangan pekerjaan..." class="form-control form-control-lg"
                                rows="3" style="resize: vertical;"></textarea>
                        </div>

                        {{-- ROW 7 --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark mb-2">Test Result</label>
                            <input type="text" name="test_result" placeholder="Hasil pengetesan... (Boleh dikosongkan)"
                                class="form-control form-control-lg" />
                        </div>

                    </div>
                </div>
            </div>

            {{-- ==================== INFORMASI TAMBAHAN ==================== --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="text-white mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">

                        {{-- ROW 1 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Jenis Pekerjaan <span
                                    class="text-danger">*</span></label>
                            <select name="job_type" required class="form-select form-select-lg">
                                <option value="">-- Pilih Jenis Pekerjaan --</option>
                                <option value="Repair">Repair</option>
                                <option value="Overhaul">Overhaul</option>
                                <option value="F.Test">F.Test</option>
                                <option value="IRAN">IRAN</option>
                                <option value="Recharging">Recharging</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Ref (CMM, etc) <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="ref" placeholder="Contoh: CMM 34-12-24" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 2 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">A/C Type</label>
                            <input type="text" name="ac_type" placeholder="Boleh dikosongkan"
                                class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Worksheet No. <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="worksheet_no" placeholder="Contoh: IN-108" required
                                class="form-control form-control-lg" />
                        </div>

                        {{-- ROW 3 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">Revision</label>
                            <input type="text" name="revision" value="1" class="form-control form-control-lg" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark mb-2">No Chapter / Zone</label>
                            <input type="text" name="zone" placeholder="Contoh: Zone 1"
                                class="form-control form-control-lg" />
                        </div>

                    </div>
                </div>
            </div>

            {{-- ==================== BUTTON ACTION ==================== --}}
            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('mws.tracking') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i>Buat MWS
                </button>
            </div>

        </form>

    </div>

    <style>
        .bg-gradient {
            background-size: 200% 200%;
        }

        .form-control-lg,
        .form-select-lg {
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
        }

        .form-control-lg:focus,
        .form-select-lg:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .text-danger {
            font-weight: 600;
        }
    </style>
@endsection
