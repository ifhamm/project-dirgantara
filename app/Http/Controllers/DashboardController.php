<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $projectId = $request->query('project');

        $projectsQuery = Project::query()->with('dockPhases');

        if ($search !== '') {
            $projectsQuery->where(function ($query) use ($search) {
                $query->where('customer', 'like', '%' . $search . '%')
                    ->orWhere('contract_no', 'like', '%' . $search . '%')
                    ->orWhere('aircraft_reg', 'like', '%' . $search . '%')
                    ->orWhere('aircraft_type', 'like', '%' . $search . '%');
            });
        }

        $projects = $projectsQuery->orderByDesc('id')->get();
        $activeProject = $projectId
            ? $projects->firstWhere('id', (int) $projectId)
            : $projects->first();

        if (!$activeProject && $projectId) {
            $activeProject = Project::with('dockPhases')->find($projectId);
        }

        $chartData = $activeProject
            ? $this->generateSCurveDataForProject($activeProject)
            : null;

        return view('dashboard', [
            'projects' => $projects,
            'activeProject' => $activeProject,
            'chartData' => $chartData,
            'search' => $search,
        ]);
    }

    private function generateSCurveDataForProject(Project $project): array
    {
        $weeks = [];
        $predictionPercentages = [];
        $actualPercentages = [];

        $totalWorkDays = (int) $project->dockPhases->sum(function ($phase) {
            return $phase->work_days ?? 0;
        });

        if ($totalWorkDays <= 0) {
            $totalWorkDays = (int) ($project->work_days ?? 84);
        }

        $totalWeeks = max(12, (int) ceil($totalWorkDays / 7));

        // Generate data for each week
        for ($week = 0; $week <= $totalWeeks; $week++) {
            $weeks[] = $week;

            // S-curve formula: percentage = 100 / (1 + exp(-k * (week - midpoint)))
            // Prediction: midpoint = totalWeeks/2, k=3 (steeper)
            $predictionMidpoint = $totalWeeks / 2;
            $predictionK = 3;
            $predictionPercentage = 100 / (1 + exp(-$predictionK * ($week - $predictionMidpoint)));

            // Actual: midpoint = (totalWeeks/2)+1, k=2.5 (slightly flatter)
            $actualMidpoint = ($totalWeeks / 2) + 1;
            $actualK = 2.5;
            $actualPercentage = 100 / (1 + exp(-$actualK * ($week - $actualMidpoint)));

            $predictionPercentages[] = round($predictionPercentage, 1);
            $actualPercentages[] = round($actualPercentage, 1);
        }

        // Format data for ECharts
        return [
            'xAxis' => $weeks,
            'series' => [
                [
                    'name' => 'Prediction',
                    'data' => $predictionPercentages,
                    'type' => 'line',
                    'smooth' => true,
                    'itemStyle' => ['color' => '#3B82F6'],
                    'areaStyle' => null,
                ],
                [
                    'name' => 'Actual',
                    'data' => $actualPercentages,
                    'type' => 'line',
                    'smooth' => true,
                    'itemStyle' => ['color' => '#EF4444'],
                    'areaStyle' => ['color' => 'rgba(239, 68, 68, 0.1)'],
                ],
            ],
        ];
    }
}
