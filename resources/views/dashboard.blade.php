<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            {{-- ── Search & Filter ─────────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <!-- Left: Searchable Dropdown for Project Chart Selection -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">
                            Pilih Proyek (Grafik)
                        </label>
                        <div x-data="{
                            open: false,
                            search: '',
                            projects: @js($allProjects),
                            selectedId: @js($activeProject?->id),
                            selectedText: @js($activeProject ? $activeProject->customer . ' (' . ($activeProject->aircraft_reg ?? 'N/A') . ')' : 'Pilih Proyek...'),
                            get filteredProjects() {
                                if (!this.search) return this.projects;
                                const q = this.search.toLowerCase();
                                return this.projects.filter(p => 
                                    p.customer.toLowerCase().includes(q) || 
                                    (p.aircraft_reg && p.aircraft_reg.toLowerCase().includes(q)) ||
                                    (p.aircraft_type && p.aircraft_type.toLowerCase().includes(q)) ||
                                    (p.contract_no && p.contract_no.toLowerCase().includes(q))
                                );
                            },
                            selectProject(id) {
                                window.location.href = '{{ route('dashboard') }}?project=' + id + '&search={{ $search }}';
                            }
                        }" class="relative w-full">
                            <button type="button" @click="open = !open" @click.outside="open = false"
                                class="w-full flex items-center justify-between rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm shadow-sm px-3 py-2 bg-white dark:bg-gray-900 text-left focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <span x-text="selectedText" class="truncate font-semibold text-gray-800 dark:text-gray-200"></span>
                                <i class="fas fa-chevron-down text-gray-400 text-xs ms-2"></i>
                            </button>

                            <div x-show="open" x-transition
                                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg max-h-60 overflow-hidden flex flex-col">
                                <div class="p-2 border-b border-gray-250 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                                    <input type="text" x-model="search" placeholder="Cari proyek..."
                                        class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-xs px-2.5 py-1.5 focus:border-blue-500 focus:ring-blue-500"
                                        @click.stop />
                                </div>
                                <div class="overflow-y-auto flex-1 py-1">
                                    <template x-for="p in filteredProjects" :key="p.id">
                                        <button type="button" @click="selectProject(p.id)"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-blue-50 dark:hover:bg-blue-900/45 flex flex-col gap-0.5 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                            :class="p.id === selectedId ? 'bg-blue-50/50 dark:bg-blue-950/30' : ''">
                                            <span class="font-bold text-gray-900 dark:text-gray-100" x-text="p.customer"></span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                <span x-text="p.aircraft_type || 'N/A'"></span> &middot; Reg. <span x-text="p.aircraft_reg || '—'"></span> &middot; No. Kontrak: <span x-text="p.contract_no || '—'"></span>
                                            </span>
                                        </button>
                                    </template>
                                    <div x-show="filteredProjects.length === 0" class="px-3 py-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                        Proyek tidak ditemukan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Search Filter for Projects Summary -->
                    <form method="GET" action="{{ route('dashboard') }}" class="flex gap-2">
                        @if($activeProject)
                            <input type="hidden" name="project" value="{{ $activeProject->id }}">
                        @endif
                        <div class="flex-1">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">
                                Filter Summary Proyek
                            </label>
                            <input name="search" type="text" value="{{ $search }}"
                                placeholder="Customer, no kontrak, reg, atau tipe..."
                                class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900
                                       dark:text-gray-100 text-sm shadow-sm
                                       focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <button type="submit"
                            class="rounded bg-blue-700 px-5 py-2 text-sm font-bold text-white
                                   hover:bg-blue-800 transition shadow-sm self-end">
                            Filter
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Active Project ──────────────────────────────────────────────── --}}
            @if($activeProject && $chartData)

                {{-- Project Info Bar --}}
                <div class="shadow-sm sm:rounded-lg overflow-hidden" style="background:#1b2f4e">
                    <div class="px-6 py-4 flex flex-wrap gap-x-10 gap-y-3 text-sm">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">Customer</div>
                            <div class="font-bold text-white">{{ $activeProject->customer }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">No. Kontrak</div>
                            <div class="font-bold text-white">{{ $activeProject->contract_no ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">S/N (Reg.)</div>
                            <div class="font-bold text-white">{{ $activeProject->aircraft_reg ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">Aircraft Type</div>
                            <div class="font-bold text-white">{{ $activeProject->aircraft_type ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">Period</div>
                            <div class="font-bold text-white">
                                {{ $activeProject->start_date?->format('d M Y') }}
                                &nbsp;–&nbsp;
                                {{ $activeProject->finish_date?->format('d M Y') }}
                            </div>
                        </div>
                        <div class="ml-auto flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-xs font-semibold uppercase tracking-widest mb-0.5" style="color:#7eb3e8">Overall Progress</div>
                                @php
                                    $pct = $activeProject->progress * 100;
                                    $pctColor = $pct >= 80 ? '#4ade80' : ($pct >= 40 ? '#fbbf24' : '#f87171');
                                @endphp
                                <div class="text-2xl font-black" style="color:{{ $pctColor }}">
                                    {{ number_format($pct, 1) }}%
                                </div>
                            </div>
                            <a href="{{ route('projects.export-excel', $activeProject->id) }}"
                               class="rounded bg-emerald-600 hover:bg-emerald-700 transition px-4 py-2 text-xs font-bold text-white shadow-sm flex items-center gap-1.5 self-center">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>

                    {{-- Progress bar strip --}}
                    <div class="h-1.5 w-full bg-white/10">
                        <div class="h-1.5 transition-all" style="width:{{ min($pct,100) }}%; background:{{ $pctColor }}"></div>
                    </div>
                </div>

                {{-- Weekly Comparison Table --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <style>
                        /* Styling scrollbar horizontal untuk container tabel */
                        .custom-table-scrollbar::-webkit-scrollbar {
                            height: 6px;
                        }
                        .custom-table-scrollbar::-webkit-scrollbar-track {
                            background: rgba(241, 245, 249, 0.5);
                            border-radius: 4px;
                        }
                        .custom-table-scrollbar::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 4px;
                            transition: background 0.2s;
                        }
                        .custom-table-scrollbar::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }
                    </style>

                    <div class="px-5 py-2.5 border-b border-gray-200 dark:border-gray-700"
                         style="background:#f0f4f8">
                        <span class="text-xs font-black uppercase tracking-widest text-gray-500">
                            Weekly Progress Comparison
                        </span>
                    </div>

                    <div class="overflow-x-auto custom-table-scrollbar">
                        <table class="border-collapse" style="min-width:max-content; font-size:11px">
                            {{-- Header row --}}
                            <thead>
                                <tr>
                                    <th class="border border-gray-500 px-3 py-2 text-left whitespace-nowrap font-bold text-white"
                                        style="min-width:110px; background:#1b2f4e">Customer</th>
                                    <th class="border border-gray-500 px-3 py-2 text-left whitespace-nowrap font-bold text-white"
                                        style="min-width:155px; background:#1b2f4e">No. Kontrak</th>
                                    <th class="border border-gray-500 px-3 py-2 text-left whitespace-nowrap font-bold text-white"
                                        style="min-width:80px; background:#1b2f4e">S/N</th>
                                    <th class="border border-gray-500 px-3 py-2 text-center whitespace-nowrap font-bold text-white"
                                        style="min-width:75px; background:#1b2f4e">KATEGORI</th>
                                    @foreach($chartData['dates'] as $index => $date)
                                        @php
                                            $weekNum = $chartData['weekNums'][$index] ?? $index;
                                        @endphp
                                        <th class="border border-gray-500 px-2 py-1 text-center whitespace-nowrap font-bold text-white"
                                            style="min-width:75px; background:#1b2f4e">
                                            <div class="text-[9px] uppercase tracking-wider text-blue-300">W{{ $weekNum }}</div>
                                            <div class="text-[10px] font-semibold mt-0.5">{{ $date }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>

                            {{-- Data rows --}}
                            <tbody>
                                {{-- PLAN --}}
                                <tr>
                                    <td rowspan="3"
                                        class="border border-gray-300 px-3 py-1.5 font-semibold align-middle"
                                        style="background:#f9fafb">
                                        {{ $activeProject->customer }}
                                    </td>
                                    <td rowspan="3"
                                        class="border border-gray-300 px-3 py-1.5 align-middle text-gray-700"
                                        style="background:#f9fafb">
                                        {{ $activeProject->contract_no ?? '—' }}
                                    </td>
                                    <td rowspan="3"
                                        class="border border-gray-300 px-3 py-1.5 align-middle text-gray-700"
                                        style="background:#f9fafb">
                                        {{ $activeProject->aircraft_reg ?? '—' }}
                                    </td>

                                    <td class="border border-gray-300 px-2 py-1.5 text-center font-black whitespace-nowrap"
                                        style="color:#1565c0; background:#dbeafe; letter-spacing:.05em">
                                        PLAN
                                    </td>
                                    @foreach($chartData['plan'] as $val)
                                        <td class="border border-gray-200 px-2 py-1.5 text-right tabular-nums"
                                            style="background:#eff6ff; color:#1d4ed8">
                                            {{ number_format($val, 2) }}%
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- ACTUAL --}}
                                <tr>
                                    <td class="border border-gray-300 px-2 py-1.5 text-center font-black whitespace-nowrap"
                                        style="color:#c2410c; background:#ffedd5; letter-spacing:.05em">
                                        ACTUAL
                                    </td>
                                    @foreach($chartData['actual'] as $val)
                                        <td class="border border-gray-200 px-2 py-1.5 text-right tabular-nums"
                                            style="background:#fff7ed; color:#ea580c">
                                            {{ $val !== null ? number_format($val, 2) . '%' : '—' }}
                                        </td>
                                    @endforeach
                                </tr>

                                {{-- DELTA --}}
                                <tr>
                                    <td class="border border-gray-300 px-2 py-1.5 text-center font-black whitespace-nowrap"
                                        style="color:#374151; background:#f3f4f6; letter-spacing:.05em">
                                        DELTA
                                    </td>
                                    @foreach($chartData['delta'] as $val)
                                        @php
                                            if ($val === null) {
                                                $bg  = '#f9fafb';
                                                $fg  = '#9ca3af';
                                                $txt = '—';
                                            } elseif ($val > 0) {
                                                $bg  = '#fef2f2';
                                                $fg  = '#dc2626';
                                                $txt = '+' . number_format($val, 2) . '%';
                                            } elseif ($val < 0) {
                                                $bg  = '#f0fdf4';
                                                $fg  = '#16a34a';
                                                $txt = number_format($val, 2) . '%';
                                            } else {
                                                $bg  = '#f9fafb';
                                                $fg  = '#6b7280';
                                                $txt = number_format($val, 2) . '%';
                                            }
                                        @endphp
                                        <td class="border border-gray-200 px-2 py-1.5 text-right font-semibold tabular-nums"
                                            style="background:{{ $bg }}; color:{{ $fg }}">
                                            {{ $txt }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Comparison Chart --}}
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div id="comparison-chart"
                         class="w-full"
                         style="height:500px"
                         data-chart='@json($chartData)'>
                    </div>
                </div>

            @else
                {{-- Empty state --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-20 text-center">
                    <svg class="mx-auto mb-4 w-14 h-14 text-gray-300 dark:text-gray-600"
                         fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0
                                 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0
                                 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-gray-400 dark:text-gray-500 text-sm">
                        Pilih proyek di bawah untuk menampilkan <strong>Comparison Chart</strong>.
                    </p>
                </div>
            @endif

            {{-- ── Projects Summary ────────────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between"
                     style="background:#f0f4f8">
                    <span class="text-xs font-black uppercase tracking-widest text-gray-500">
                        Projects Summary
                    </span>
                    <a href="{{ route('projects.create') }}"
                       class="text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 transition px-3 py-1.5 rounded shadow-sm flex items-center gap-1.5">
                        <i class="fas fa-plus"></i> New Project
                    </a>
                </div>

                <div class="p-4">
                    @if($projects->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($projects as $project)
                                @php
                                    $p        = round($project->progress * 100, 1);
                                    $isActive = $activeProject && $activeProject->id === $project->id;
                                    $barClr   = $p >= 80 ? '#22c55e' : ($p >= 40 ? '#f59e0b' : '#ef4444');
                                    $txtClr   = $p >= 80 ? 'text-green-600' : ($p >= 40 ? 'text-yellow-500' : 'text-red-500');
                                @endphp
                                <div class="rounded-lg border p-4 transition hover:shadow-md
                                    {{ $isActive
                                        ? 'border-blue-500 ring-1 ring-blue-400 bg-blue-50 dark:bg-blue-950'
                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">

                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1 mr-3">
                                            <div class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                                {{ $project->customer }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $project->aircraft_type ?? 'N/A' }}
                                                @if($project->aircraft_reg)
                                                    &nbsp;·&nbsp; Reg. {{ $project->aircraft_reg }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                                {{ $project->contract_no ?? 'No contract' }}
                                            </div>
                                        </div>
                                        <span class="text-lg font-black {{ $txtClr }}">{{ $p }}%</span>
                                    </div>

                                    {{-- Mini progress bar --}}
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 mb-2">
                                        <div class="h-1.5 rounded-full transition-all"
                                             style="width:{{ $p }}%; background:{{ $barClr }}"></div>
                                    </div>

                                    <div class="text-xs text-gray-400 dark:text-gray-500 mb-3">
                                        {{ $project->start_date?->format('d M Y') ?? '?' }}
                                        &nbsp;→&nbsp;
                                        {{ $project->finish_date?->format('d M Y') ?? '?' }}
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('dashboard', ['project' => $project->id, 'search' => $search]) }}"
                                           class="flex-1 text-center rounded px-2 py-1.5 text-xs font-bold
                                                  text-white bg-blue-700 hover:bg-blue-800 transition">
                                            Lihat Chart
                                        </a>
                                        <a href="{{ route('projects.show', $project) }}"
                                           class="flex-1 text-center rounded border border-gray-300 dark:border-gray-600
                                                  px-2 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200
                                                  hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination links --}}
                        <div class="mt-5">
                            {{ $projects->links() }}
                        </div>
                    @else
                        <div class="text-center py-10 text-sm text-gray-400">
                            Belum ada proyek.
                            <a href="{{ route('projects.create') }}"
                               class="text-blue-600 hover:underline font-medium">Buat proyek baru</a>
                        </div>
                    @endif
                </div>
            </div>

        </div>{{-- /max-w-full --}}
    </div>

    {{-- ── Chart Script ─────────────────────────────────────────────────────── --}}
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('comparison-chart');
        if (!el) return;

        const data  = JSON.parse(el.dataset.chart);
        const chart = echarts.init(el, null, { renderer: 'canvas' });

        const totalWeeks = data.totalWeeks ?? data.weekNums.length;
        let zoomEnd = 100;
        if (totalWeeks > 12) {
            zoomEnd = Math.round((12 / totalWeeks) * 100);
        }

        chart.setOption({
            backgroundColor: '#ffffff',

            title: {
                text: 'COMPARISON CHART',
                left: 'center',
                top: 14,
                textStyle: {
                    fontSize: 13,
                    fontWeight: 'bold',
                    color: '#111827',
                    fontFamily: 'Arial, sans-serif',
                    letterSpacing: '0.2em',
                },
            },

            tooltip: {
                trigger: 'axis',
                backgroundColor: 'rgba(255,255,255,0.97)',
                borderColor: '#e5e7eb',
                borderWidth: 1,
                padding: [10, 14],
                textStyle: { color: '#374151', fontSize: 12 },
                formatter: function (params) {
                    const idx  = params[0].dataIndex;
                    const week = data.weekNums[idx];
                    const date = data.dates[idx] ?? '';
                    let html = '<div style="font-weight:800;margin-bottom:6px">'
                             + 'Week ' + week
                             + ' &nbsp;<span style="color:#9ca3af;font-size:10px;font-weight:400">' + date + '</span>'
                             + '</div>';
                    params.forEach(function (p) {
                        if (p.value !== null && p.value !== undefined) {
                            html += '<div style="display:flex;justify-content:space-between;gap:20px">'
                                  + '<span>' + p.marker + ' ' + p.seriesName + '</span>'
                                  + '<b>' + p.value + '%</b>'
                                  + '</div>';
                        }
                    });
                    var delta = data.delta[idx];
                    if (delta !== null && delta !== undefined) {
                        var clr = delta > 0 ? '#dc2626' : (delta < 0 ? '#16a34a' : '#6b7280');
                        var sign = delta > 0 ? '+' : '';
                        html += '<div style="margin-top:6px;padding-top:6px;border-top:1px solid #f3f4f6;'
                              + 'display:flex;justify-content:space-between;gap:20px">'
                              + '<span style="color:#6b7280">Delta</span>'
                              + '<b style="color:' + clr + '">' + sign + delta + '%</b>'
                              + '</div>';
                    }
                    return html;
                },
            },

            legend: {
                data: ['PLAN', 'ACTUAL'],
                bottom: 2,
                icon: 'circle',
                itemWidth: 10,
                itemHeight: 10,
                textStyle: { fontSize: 11, fontWeight: 'bold', color: '#374151' },
            },

            grid: {
                left: '2%',
                right: '2%',
                top: '12%',
                bottom: totalWeeks > 12 ? '16%' : '10%',
                containLabel: true,
            },

            dataZoom: [
                {
                    type: 'slider',
                    show: totalWeeks > 12,
                    xAxisIndex: [0],
                    bottom: 25,
                    height: 18,
                    borderColor: 'transparent',
                    fillerColor: 'rgba(29, 78, 216, 0.15)',
                    handleIcon: 'path://M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                    handleSize: '80%',
                    handleStyle: {
                        color: '#1d4ed8',
                        shadowBlur: 3,
                        shadowColor: 'rgba(0, 0, 0, 0.2)',
                        shadowOffsetX: 1,
                        shadowOffsetY: 1
                    },
                    textStyle: {
                        color: '#374151',
                        fontSize: 9
                    },
                    start: 0,
                    end: zoomEnd
                },
                {
                    type: 'inside',
                    xAxisIndex: [0]
                }
            ],

            xAxis: {
                type: 'category',
                data: data.weekNums,
                name: 'WEEK',
                nameLocation: 'middle',
                nameGap: 35,
                nameTextStyle: { fontWeight: 'bold', fontSize: 11, color: '#374151' },
                boundaryGap: false,
                axisLine:  { lineStyle: { color: '#9ca3af' } },
                axisTick:  { lineStyle: { color: '#d1d5db' } },
                splitLine: { show: true, lineStyle: { color: '#f3f4f6', type: 'solid' } },
                axisLabel: {
                    fontSize: 9,
                    color: '#6b7280',
                    formatter: function (value, index) {
                        const date = data.dates[index] ? data.dates[index] : '';
                        return 'Week ' + value + '\n' + date;
                    }
                },
            },

            yAxis: {
                type: 'value',
                name: 'PERCENTAGE (%)',
                nameLocation: 'middle',
                nameGap: 55,
                nameRotate: 90,
                nameTextStyle: { fontWeight: 'bold', fontSize: 11, color: '#374151' },
                min: 0,
                max: 120,
                interval: 20,
                axisLine:  { show: true, lineStyle: { color: '#9ca3af' } },
                axisTick:  { show: true, lineStyle: { color: '#d1d5db' } },
                splitLine: { lineStyle: { color: '#f3f4f6', type: 'solid' } },
                axisLabel: {
                    fontSize: 10,
                    color: '#6b7280',
                    formatter: function (v) { return v + '%'; },
                },
            },

            series: [
                {
                    name: 'PLAN',
                    type: 'line',
                    data: data.plan,
                    smooth: false,
                    itemStyle:  { color: '#1d4ed8' },
                    lineStyle:  { color: '#1d4ed8', width: 1.8 },
                    symbol:     'circle',
                    symbolSize: 5,
                    label: {
                        show: true,
                        position: 'top',
                        fontSize: 8,
                        color: '#1d4ed8',
                        fontWeight: 'bold',
                        distance: 2,
                        formatter: function (p) {
                            return p.value !== null ? p.value + '%' : '';
                        },
                    },
                    markArea: {
                        silent: true,
                        data: [
                            [
                                {
                                    name: 'PRE DOCK\n(20% Progress)',
                                    xAxis: 0,
                                    itemStyle: { color: 'rgba(59, 130, 246, 0.05)' },
                                    label: {
                                        show: true,
                                        position: 'insideTopLeft',
                                        distance: 15,
                                        color: '#2563eb',
                                        fontStyle: 'italic',
                                        fontWeight: 'bold',
                                        fontSize: 9
                                    }
                                },
                                {
                                    xAxis: data.predockEndWeek
                                }
                            ],
                            [
                                {
                                    name: 'IN DOCK\n(60% Progress)',
                                    xAxis: data.predockEndWeek,
                                    itemStyle: { color: 'rgba(245, 158, 11, 0.05)' },
                                    label: {
                                        show: true,
                                        position: 'insideTopLeft',
                                        distance: 15,
                                        color: '#d97706',
                                        fontStyle: 'italic',
                                        fontWeight: 'bold',
                                        fontSize: 9
                                    }
                                },
                                {
                                    xAxis: data.indockEndWeek
                                }
                            ],
                            [
                                {
                                    name: 'POST DOCK\n(20% Progress)',
                                    xAxis: data.indockEndWeek,
                                    itemStyle: { color: 'rgba(16, 185, 129, 0.05)' },
                                    label: {
                                        show: true,
                                        position: 'insideTopLeft',
                                        distance: 15,
                                        color: '#059669',
                                        fontStyle: 'italic',
                                        fontWeight: 'bold',
                                        fontSize: 9
                                    }
                                },
                                {
                                    xAxis: data.totalWeeks
                                }
                            ]
                        ]
                    },
                    markLine: {
                        symbol: ['none', 'none'],
                        lineStyle: {
                            type: 'dashed',
                            color: '#9ca3af',
                            width: 1
                        },
                        label: {
                            show: true,
                            position: 'end',
                            fontSize: 9,
                            color: '#4b5563',
                            fontWeight: 'bold',
                            formatter: '{b}'
                        },
                        data: [
                            {
                                name: 'Pre-Dock End (20%)',
                                xAxis: data.predockEndWeek
                            },
                            {
                                name: 'In-Dock End (80%)',
                                xAxis: data.indockEndWeek
                            }
                        ]
                    }
                },
                {
                    name: 'ACTUAL',
                    type: 'line',
                    data: data.actual,
                    connectNulls: false,
                    smooth: false,
                    itemStyle:  { color: '#ea580c' },
                    lineStyle:  { color: '#ea580c', width: 1.8 },
                    symbol:     'circle',
                    symbolSize: 5,
                    label: {
                        show: true,
                        position: 'bottom',
                        fontSize: 8,
                        color: '#ea580c',
                        fontWeight: 'bold',
                        distance: 2,
                        formatter: function (p) {
                            return p.value !== null ? p.value + '%' : '';
                        },
                    },
                },
            ],
        });

        window.addEventListener('resize', function () { chart.resize(); });
    });
    </script>
    @endpush
</x-app-layout>
