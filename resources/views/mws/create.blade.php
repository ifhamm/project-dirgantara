@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Buat MWS Baru</h1>

    <form action="{{ route('mws.store') }}" method="POST" class="space-y-8">
        @csrf

        {{-- ===================== --}}
        {{-- DETAIL PEKERJAAN --}}
        {{-- ===================== --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-bold mb-4">Detail Pekerjaan</h2>

            <div class="grid grid-cols-2 gap-4">

                <input type="text" name="ref_logistic_ppc" placeholder="Ref Logistic PPC" class="input" />

                <input type="text" name="customer_name" placeholder="Customer" required class="input" />

                <input type="text" name="wbs_no" placeholder="WBS No" required class="input" />

                <input type="text" name="title" placeholder="Part Name / Title" required class="input col-span-2" />

                <input type="text" name="part_number" placeholder="Part Number" required class="input" />

                <input type="text" name="serial_number" placeholder="Serial Number" required class="input" />

                <input type="text" name="mdr_doc_defect" placeholder="MDR Doc Defect" class="input col-span-2" />

                <input type="text" name="capability" placeholder="Capability" class="input" />

                <input type="text" name="shop_area" placeholder="Shop Area" required class="input" />

                <textarea name="remark_mws" placeholder="Remark MWS" class="input col-span-2"></textarea>

                <input type="text" name="test_result" placeholder="Test Result" class="input col-span-2" />

            </div>
        </div>

        {{-- ===================== --}}
        {{-- INFORMASI TAMBAHAN --}}
        {{-- ===================== --}}
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-bold mb-4">Informasi Tambahan</h2>

            <div class="grid grid-cols-2 gap-4">

                {{-- JOB TYPE --}}
                <select name="job_type" required class="input">
                    <option value="">Pilih Job Type</option>
                    <option value="Repair">Repair</option>
                    <option value="Overhaul">Overhaul</option>
                    <option value="F.Test">F.Test</option>
                    <option value="IRAN">IRAN</option>
                    <option value="Recharging">Recharging</option>
                </select>

                <input type="text" name="ref" placeholder="Ref (CMM, etc)" required class="input" />

                <input type="text" name="ac_type" placeholder="A/C Type" class="input" />

                <input type="text" name="worksheet_no" placeholder="Worksheet No" required class="input" />

                <input type="text" name="revision" value="1" class="input" />

                <input type="text" name="zone" placeholder="Zone" class="input" />

            </div>
        </div>

        {{-- BUTTON --}}
        <div class="flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">
                Simpan MWS
            </button>
        </div>

    </form>
</div>

{{-- STYLE SIMPLE --}}
<style>
.input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
</style>

@endsection