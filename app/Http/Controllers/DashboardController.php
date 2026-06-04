<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query("search", ""));
        $projectId = $request->query("project");

        $projectsQuery = Project::query()->with("dockPhases");

        if ($search !== "") {
            $projectsQuery->where(function ($query) use ($search) {
                $query
                    ->where("customer", "like", "%" . $search . "%")
                    ->orWhere("contract_no", "like", "%" . $search . "%")
                    ->orWhere("aircraft_reg", "like", "%" . $search . "%")
                    ->orWhere("aircraft_type", "like", "%" . $search . "%");
            });
        }

        $allProjects = Project::select('id', 'customer', 'aircraft_type', 'aircraft_reg', 'contract_no')
            ->orderByDesc('id')
            ->get();

        $activeProject = null;
        if ($projectId) {
            $activeProject = Project::with("dockPhases")->find($projectId);
        }
        if (!$activeProject) {
            $activeProject = Project::with("dockPhases")->orderByDesc("id")->first();
        }

        $projects = $projectsQuery->orderByDesc("id")->paginate(6)->withQueryString();

        $chartData = $activeProject
            ? $this->buildChartData($activeProject)
            : null;

        return view("dashboard", [
            "projects" => $projects,
            "allProjects" => $allProjects,
            "activeProject" => $activeProject,
            "chartData" => $chartData,
            "search" => $search,
        ]);
    }

    // -------------------------------------------------------------------------

    private function buildChartData(Project $project): ?array
    {
        $startDate = $project->start_date;
        $finishDate = $project->finish_date;

        if (!$startDate || !$finishDate) {
            return null;
        }

        $today = now()->startOfDay();
        $currentPct = round((float) $project->progress * 100, 2);

        $totalDays = max(1, (int) $startDate->diffInDays($finishDate));
        $totalWeeks = (int) ceil($totalDays / 7);

        // How many weeks of the project have elapsed (capped at totalWeeks)
        if ($today <= $startDate) {
            $elapsedWeeks = 0;
        } elseif ($today >= $finishDate) {
            $elapsedWeeks = $totalWeeks;
        } else {
            $elapsedWeeks = (int) min(
                $totalWeeks,
                ceil((int) $startDate->diffInDays($today) / 7),
            );
        }

        $weekNums = [];
        $dates = [];
        $plan = [];
        $actual = [];
        $delta = [];

        // S-Curve calculation: S(x) = 0.5 * (1 - cos(x * pi))
        $sElapsed = 0.5 * (1 - cos((($elapsedWeeks) / max(1, $totalWeeks)) * M_PI));

        for ($w = 0; $w <= $totalWeeks; $w++) {
            $weekNums[] = $w;

            // Date for this reporting point
            $pointDate = $startDate->copy()->addDays($w * 7);
            if ($pointDate > $finishDate) {
                $pointDate = $finishDate->copy();
            }
            $dates[] = $pointDate->format("d-M-y");

            // PLAN S-Curve
            $x = $w / max(1, $totalWeeks);
            $sVal = 0.5 * (1 - cos($x * M_PI));
            $planVal = round($sVal * 100, 2);
            $plan[] = $planVal;

            // ACTUAL: follows the S-Curve shape scaled to currentPct
            if ($w <= $elapsedWeeks) {
                $actualVal = 0.0;
                if ($elapsedWeeks > 0 && $sElapsed > 0) {
                    $sW = 0.5 * (1 - cos($x * M_PI));
                    $actualVal = round($currentPct * ($sW / $sElapsed), 2);
                } elseif ($w === $elapsedWeeks) {
                    $actualVal = $currentPct;
                }
                $actual[] = $actualVal;
                $delta[] = round($planVal - $actualVal, 2);
            } else {
                $actual[] = null;
                $delta[] = null;
            }
        }

        // Calculate phase boundaries on the S-Curve:
        // Pre-Dock ends at 20% progress (x = acos(0.60) / pi)
        // In-Dock ends at 80% progress (x = acos(-0.60) / pi)
        $x_predock = acos(0.60) / M_PI;
        $x_indock = acos(-0.60) / M_PI;

        $predockEndWeek = max(0, min($totalWeeks, (int) round($x_predock * $totalWeeks)));
        $indockEndWeek = max($predockEndWeek, min($totalWeeks, (int) round($x_indock * $totalWeeks)));

        return [
            "weekNums" => $weekNums,
            "dates" => $dates,
            "plan" => $plan,
            "actual" => $actual,
            "delta" => $delta,
            "totalWeeks" => $totalWeeks,
            "elapsedWeeks" => $elapsedWeeks,
            "currentPct" => $currentPct,
            "predockEndWeek" => $predockEndWeek,
            "indockEndWeek" => $indockEndWeek,
        ];
    }
}
