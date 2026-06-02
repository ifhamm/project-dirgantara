<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3 md:flex-row md:items-end">
                        <div class="flex-1">
                            <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search Project
                            </label>
                            <input
                                id="search"
                                name="search"
                                type="text"
                                value="{{ $search }}"
                                placeholder="Customer, contract no, aircraft reg, or aircraft type"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-700 transition"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <!-- Project Progress Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Project Progress (S-Curve)
                        </h3>
                        @if($activeProject)
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Showing: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $activeProject->customer }}</span>
                                @if($activeProject->aircraft_reg)
                                    • Reg {{ $activeProject->aircraft_reg }}
                                @endif
                            </div>
                        @endif
                    </div>

                    @if($chartData)
                        <div id="project-progress-chart"
                             class="w-full h-96"
                             data-echarts-chart-data='@json($chartData)'>
                        </div>
                    @else
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <p>No project selected. Use search or pick a project below.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Projects Summary -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Projects Summary
                    </h3>
                    @if($projects->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($projects as $project)
                                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $project->customer }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $project->contract_no ?: 'No contract' }}
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a href="{{ route('dashboard', ['project' => $project->id, 'search' => $search]) }}"
                                           class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                                            Lihat Chart
                                        </a>
                                        <a href="{{ route('projects.show', $project) }}"
                                           class="inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            Detail Project
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">
                            No projects yet. <a href="{{ route('projects.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Create one</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartElement = document.getElementById('project-progress-chart');
                if (!chartElement) return;

                const chartData = JSON.parse(chartElement.dataset.echartsChartData);
                
                // Initialize ECharts
                const chart = echarts.init(chartElement);
                
                const option = {
                    title: {
                        text: '',
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(params) {
                            let result = `Week ${params[0].axisValue}<br>`;
                            params.forEach(param => {
                                result += `${param.marker} ${param.seriesName}: ${param.value}%<br>`;
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: ['Prediction', 'Actual'],
                        bottom: 0,
                    },
                    grid: {
                        left: '3%',
                        right: '3%',
                        bottom: '10%',
                        top: '5%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: chartData.xAxis,
                        name: 'Week',
                    },
                    yAxis: {
                        type: 'value',
                        name: 'Progress (%)',
                        max: 120,
                        min: 0,
                    },
                    series: chartData.series.map(serie => ({
                        ...serie,
                        label: {
                            show: true,
                            position: 'top',
                            fontSize: 10,
                            formatter: '{c}%'
                        },
                        smooth: true,
                    }))
                };
                
                chart.setOption(option);
                
                // Responsive resize
                window.addEventListener('resize', function() {
                    chart.resize();
                });
            });
        </script>
    @endpush
</x-app-layout>
