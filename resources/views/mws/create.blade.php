@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">

    <h1 class="mb-4">Buat MWS Baru</h1>

    <form action="{{ route('mws.store') }}" method="POST">
        @csrf

        {{-- ===================== --}}
        {{-- DETAIL PEKERJAAN --}}
        {{-- ===================== --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Detail Pekerjaan</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <input type="text" name="ref_logistic_ppc" placeholder="Ref Logistic PPC" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="customer_name" placeholder="Customer" required class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="wbs_no" placeholder="WBS No" required class="form-control" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <input type="text" name="title" placeholder="Part Name / Title" required class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="part_number" placeholder="Part Number" required class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="serial_number" placeholder="Serial Number" required class="form-control" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <input type="text" name="mdr_doc_defect" placeholder="MDR Doc Defect" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="capability" placeholder="Capability" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="shop_area" placeholder="Shop Area" required class="form-control" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <textarea name="remark_mws" placeholder="Remark MWS" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="col-md-12 mb-3">
                        <input type="text" name="test_result" placeholder="Test Result" class="form-control" />
                    </div>

                </div>
            </div>
        </div>

        {{-- ===================== --}}
        {{-- INFORMASI TAMBAHAN --}}
        {{-- ===================== --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Informasi Tambahan</h5>
            </div>
            <div class="card-body">
                <div class="row">

                    {{-- JOB TYPE --}}
                    <div class="col-md-6 mb-3">
                        <select name="job_type" required class="form-select">
                            <option value="">Pilih Job Type</option>
                            <option value="Repair">Repair</option>
                            <option value="Overhaul">Overhaul</option>
                            <option value="F.Test">F.Test</option>
                            <option value="IRAN">IRAN</option>
                            <option value="Recharging">Recharging</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="ref" placeholder="Ref (CMM, etc)" required class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="ac_type" placeholder="A/C Type" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="worksheet_no" placeholder="Worksheet No" required class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="revision" value="1" class="form-control" />
                    </div>

                    <div class="col-md-6 mb-3">
                        <input type="text" name="zone" placeholder="Zone" class="form-control" />
                    </div>

                </div>
            </div>
        </div>

        {{-- BUTTON --}}
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-primary">
                Simpan MWS
            </button>
        </div>

    </form>
</div>

@endsection