<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print MWS - {{ $mwsPart->part_id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
            background-color: #eee;
            margin: 0;
            padding: 0;
        }

        /* Outer container: styled as a vertical A4 sheet in the browser */
        .print-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            box-sizing: border-box;
        }

        /* Header Grid Table */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .header-table td {
            border: 1.5px solid #000;
            padding: 8px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 25%;
            text-align: center;
        }

        .logo-img {
            max-height: 45px;
            display: block;
            margin: 0 auto 5px;
        }

        .logo-text {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .title-cell {
            width: 50%;
            text-align: center;
        }

        .title-cell h1 {
            font-size: 16px;
            margin: 0 0 6px 0;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .mws-info-line {
            font-size: 9px;
            text-align: left;
            margin-top: 4px;
        }

        .meta-cell {
            width: 25%;
            font-size: 9px;
            line-height: 1.5;
        }

        .sub-header-row td {
            font-size: 9px;
            background-color: #f5f5f5;
            font-weight: bold;
        }

        /* Section Titles */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 5px 8px;
            border: 1.5px solid #000;
            border-bottom: none;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }

        /* Tables styling */
        .worksheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .worksheet-table th, 
        .worksheet-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            text-align: left;
        }

        .worksheet-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            text-align: center;
        }

        /* Column Specific Widths */
        .col-no { width: 5%; text-align: center; font-weight: bold; }
        .col-desc { width: 53%; }
        .col-plan { width: 10%; text-align: center; font-size: 9px; }
        .col-act { width: 12%; text-align: left; font-size: 9px; }
        .col-tech { width: 10%; text-align: center; font-weight: bold; }
        .col-insp { width: 10%; text-align: center; font-weight: bold; }

        /* Step Inner Components */
        .step-desc-text {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .caution-box {
            background-color: #fff3cd;
            border: 1px solid #ffecb5;
            padding: 6px 8px;
            margin: 6px 0;
            border-radius: 3px;
            font-size: 9.5px;
        }

        .caution-title {
            color: #856404;
            font-weight: bold;
            text-transform: uppercase;
        }

        .note-box {
            background-color: #cce5ff;
            border: 1px solid #b8daff;
            padding: 6px 8px;
            margin: 6px 0;
            border-radius: 3px;
            font-size: 9.5px;
        }

        .note-title {
            color: #004085;
            font-weight: bold;
            text-transform: uppercase;
        }

        .details-list {
            margin: 6px 0;
            padding-left: 15px;
        }

        .details-list li {
            margin-bottom: 2px;
        }

        .substeps-container {
            margin: 6px 0;
        }

        .substep-row {
            display: flex;
            margin-bottom: 3px;
            padding-left: 5px;
        }

        .substep-label {
            font-weight: bold;
            margin-right: 6px;
            width: 15px;
        }

        .inline-attachments {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #ccc;
        }

        .inline-attachment-item {
            font-size: 9px;
            color: #555;
            margin-bottom: 2px;
        }

        .inline-attachment-img {
            max-width: 120px;
            max-height: 80px;
            display: block;
            margin: 4px 0;
            border: 1px solid #ddd;
            border-radius: 2px;
        }

        /* Signatures section */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signatures-table th {
            border: 1.5px solid #000;
            background-color: #f5f5f5;
            padding: 6px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .signatures-table td {
            border: 1.5px solid #000;
            padding: 12px;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            height: 70px;
        }

        .sig-badge {
            display: inline-block;
            border: 1.5px solid #28a745;
            color: #28a745;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 3px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .sig-name {
            font-weight: bold;
            font-size: 10px;
            text-decoration: underline;
        }

        .sig-date {
            font-size: 8.5px;
            color: #666;
            margin-top: 2px;
        }

        .sig-line {
            color: #aaa;
            margin-bottom: 5px;
        }

        /* Appendix */
        .page-break {
            page-break-before: always;
        }

        .appendix-container {
            margin-top: 20px;
            text-align: center;
        }

        .appendix-title {
            font-size: 13px;
            font-weight: bold;
            text-align: left;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .appendix-item {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .appendix-item h3 {
            font-size: 11px;
            text-align: left;
            margin: 0 0 10px 0;
            background-color: #f5f5f5;
            padding: 5px;
            border-left: 4px solid #000;
        }

        .appendix-img {
            max-width: 100%;
            height: auto;
            max-height: 200mm;
            border: 1px solid #000;
            padding: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        /* Print utilities */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
                margin: 0;
                padding: 0;
            }
            .print-container {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="print-container">

    <!-- HEADER BLOCK -->
    <table class="header-table">
        <tr>
            <td rowspan="2" class="logo-cell">
                @if(file_exists(public_path('logo-di.png')))
                    <img src="{{ asset('logo-di.png') }}" class="logo-img" alt="Logo">
                @endif
                <div class="logo-text">PT DIRGANTARA INDONESIA (Persero)</div>
            </td>
            <td rowspan="2" class="title-cell">
                <h1>MAINTENANCE WORK SHEET</h1>
                <div class="mws-info-line"><strong>Title:</strong> {{ $mwsPart->title }}</div>
                <div class="mws-info-line"><strong>Ref:</strong> {{ $mwsPart->ref ?? 'N/A' }}</div>
            </td>
            <td class="meta-cell">
                <strong>MWS :</strong> {{ $mwsPart->part_id }}<br>
                <strong>IWO/WO NO :</strong> {{ $mwsPart->iwo_no }}
            </td>
        </tr>
        <tr>
            <td class="meta-cell">
                <strong>WBS NO :</strong> {{ $mwsPart->wbs_no ?? 'N/A' }}<br>
                <strong>REVISION :</strong> {{ $mwsPart->revision ?? '1' }}
            </td>
        </tr>
        <tr class="sub-header-row">
            <td colspan="2">
                CUSTOMER: {{ $mwsPart->customer_name ?? '-' }} &nbsp;&nbsp;|&nbsp;&nbsp;
                A/C TYPE: {{ $mwsPart->ac_type ?? 'N/A' }} &nbsp;&nbsp;|&nbsp;&nbsp;
                SHOP AREA: {{ $mwsPart->shop_area ?? 'N/A' }} &nbsp;&nbsp;|&nbsp;&nbsp;
                ZONE: {{ $mwsPart->zone ?? 'N/A' }}
            </td>
            <td>
                JOB TYPE: {{ $mwsPart->job_type ?? 'N/A' }}
            </td>
        </tr>
    </table>

    <!-- CONSUMABLES BLOCK (CONDITIONAL) -->
    @if($mwsPart->consumables->isNotEmpty())
        <div class="section-title">CONSUMABLE MATERIALS</div>
        <table class="worksheet-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 40%;">Name</th>
                    <th style="width: 45%;">Identification / References</th>
                    <th style="width: 15%; text-align: center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mwsPart->consumables as $consumable)
                    <tr>
                        <td>{{ $consumable->name }}</td>
                        <td>{{ $consumable->identification ?? '-' }}</td>
                        <td style="text-align: center;">{{ $consumable->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- STEPS BLOCK -->
    <div class="section-title">PROCEDURES & STEPS</div>
    <table class="worksheet-table">
        <thead>
            <tr>
                <th class="col-no">NO</th>
                <th class="col-desc">DESCRIPTION</th>
                <th class="col-plan">PLAN<br><small>(MAN/HRS)</small></th>
                <th class="col-act">ACTUAL<br><small>(MAN/HRS)</small></th>
                <th class="col-tech">TECH</th>
                <th class="col-insp">INSP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mwsPart->steps->sortBy('no') as $step)
                @php
                    $stepMechanics = $step->mechanics ?? collect();
                    $techApproved = $step->tech === 'Approved';
                    $stepCompleted = $step->status === 'completed';
                @endphp
                <tr>
                    <td class="col-no">{{ $step->no }}</td>
                    <td class="col-desc">
                        <!-- Step Main Text -->
                        <div class="step-desc-text">{{ $step->description }}</div>

                        <!-- Caution -->
                        @if($step->caution)
                            <div class="caution-box">
                                <span class="caution-title">CAUTION:</span> {{ $step->caution }}
                            </div>
                        @endif

                        <!-- Note -->
                        @if($step->note)
                            <div class="note-box">
                                <span class="note-title">NOTE:</span> {{ $step->note }}
                            </div>
                        @endif

                        <!-- Details (Bullet list) -->
                        @if(!empty($step->details))
                            <ul class="details-list">
                                @foreach($step->details as $detail)
                                    <li>{{ $detail }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <!-- Substeps -->
                        @if($step->subSteps->isNotEmpty())
                            <div class="substeps-container">
                                @foreach($step->subSteps as $sub)
                                    <div class="substep-row">
                                        <span class="substep-label">{{ $sub->label }}.</span>
                                        <span>{{ $sub->description }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Step Attachments (Inline List/Mini Previews) -->
                        @if(!empty($step->attachments))
                            <div class="inline-attachments">
                                <strong>Attachments:</strong>
                                @foreach($step->attachments as $attachment)
                                    @php
                                        $ext = strtolower(pathinfo($attachment['original_filename'] ?? $attachment['file_url'], PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true);
                                    @endphp
                                    <div class="inline-attachment-item">
                                        @if($isImg)
                                            <span>📷 {{ $attachment['original_filename'] }}</span>
                                            <img src="{{ $attachment['file_url'] }}" class="inline-attachment-img" alt="attachment">
                                        @else
                                            <span>📎 <a href="{{ $attachment['file_url'] }}" target="_blank">{{ $attachment['original_filename'] }}</a></span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <!-- Plan -->
                    <td class="col-plan">
                        <div>{{ $step->plan_man ?? '-' }} Man</div>
                        <div style="margin-top: 4px; font-weight: 500;">
                            {{ $step->plan_hours ? (number_format((float)$step->plan_hours, 1) . ' Hrs') : '-' }}
                        </div>
                    </td>

                    <!-- Actual -->
                    <td class="col-act">
                        @if($stepMechanics->isNotEmpty())
                            <div style="font-weight: bold; margin-bottom: 3px;">
                                {{ $stepMechanics->count() }} Man:
                            </div>
                            @foreach($stepMechanics as $mech)
                                <div style="font-size: 8px; color: #555;">• {{ $mech->name }} ({{ $mech->nik }})</div>
                            @endforeach
                        @else
                            <div style="color: #999; font-style: italic;">No mechanics</div>
                        @endif
                        <div style="margin-top: 6px; font-weight: bold;">
                            Hrs: {{ $step->hours }}
                        </div>
                    </td>

                    <!-- Tech -->
                    <td class="col-tech">
                        @if($techApproved)
                            <div class="sig-badge">APPROVED</div>
                            @if($stepMechanics->isNotEmpty())
                                <div style="font-size: 8px; font-weight: normal; margin-top: 2px;">
                                    by {{ $stepMechanics->first()->name }}
                                </div>
                            @endif
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>

                    <!-- Insp -->
                    <td class="col-insp">
                        @if($stepCompleted)
                            <div class="sig-badge" style="border-color: #007bff; color: #007bff;">INSPECTED</div>
                            @if($step->status_s_us)
                                <div style="font-size: 8px; font-weight: bold; color: #dc3545; margin-top: 4px;">
                                    [{{ $step->status_s_us }}]
                                </div>
                            @endif
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SIGNATURES BLOCK -->
    @if($mwsPart->approved_by || $mwsPart->status === 'completed')
        <div class="page-break"></div>
    @endif
    <table class="signatures-table">
        <thead>
            <tr>
                <th>PREPARED BY</th>
                <th>VERIFIED BY</th>
                <th>APPROVED BY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Prepared By -->
                <td>
                    @if($mwsPart->prepared_by)
                        <div class="sig-badge">SIGNED</div>
                        <div class="sig-name">{{ $mwsPart->prepared_by }}</div>
                        <div class="sig-date">{{ \Carbon\Carbon::parse($mwsPart->prepared_date)->format('d/m/Y') }}</div>
                    @else
                        <div class="sig-line">_______________________</div>
                        <div style="font-size: 8px; color: #999;">Date: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </div>
                    @endif
                </td>

                <!-- Verified By -->
                <td>
                    @if($mwsPart->verified_by)
                        <div class="sig-badge">SIGNED</div>
                        <div class="sig-name">{{ $mwsPart->verified_by }}</div>
                        <div class="sig-date">{{ \Carbon\Carbon::parse($mwsPart->verified_at)->format('d/m/Y') }}</div>
                    @else
                        <div class="sig-line">_______________________</div>
                        <div style="font-size: 8px; color: #999;">Date: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </div>
                    @endif
                </td>

                <!-- Approved By -->
                <td>
                    @if($mwsPart->approved_by)
                        <div class="sig-badge">SIGNED</div>
                        <div class="sig-name">{{ $mwsPart->approved_by }}</div>
                        <div class="sig-date">{{ \Carbon\Carbon::parse($mwsPart->approved_date)->format('d/m/Y') }}</div>
                    @else
                        <div class="sig-line">_______________________</div>
                        <div style="font-size: 8px; color: #999;">Date: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

</div>

<script>
    // Trigger automatic print dialog when loaded (optional but standard for print routes)
    window.onload = function() {
        // window.print();
    }
</script>
</body>
</html>