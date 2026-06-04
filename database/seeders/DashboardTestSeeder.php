<?php

namespace Database\Seeders;

use App\Models\DockPhase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use Illuminate\Database\Seeder;

class DashboardTestSeeder extends Seeder
{
    /**
     * Seed data proyek perawatan pesawat untuk keperluan dashboard.
     */
    public function run(): void
    {
        $projects = $this->projectDefinitions();

        foreach ($projects as $projectData) {
            $phases = $projectData["phases"];
            unset($projectData["phases"]);

            $project = Project::create($projectData);

            foreach ($phases as $phaseData) {
                $taskGroups = $phaseData["task_groups"] ?? [];
                unset($phaseData["task_groups"]);

                $phase = DockPhase::create(
                    array_merge($phaseData, [
                        "project_id" => $project->id,
                    ]),
                );

                foreach ($taskGroups as $groupData) {
                    $tasks = $groupData["tasks"] ?? [];
                    unset($groupData["tasks"]);

                    $group = TaskGroup::create(
                        array_merge($groupData, [
                            "dock_phase_id" => $phase->id,
                        ]),
                    );

                    foreach ($tasks as $taskData) {
                        Task::create(
                            array_merge($taskData, [
                                "task_group_id" => $group->id,
                            ]),
                        );
                    }
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Data
    // -------------------------------------------------------------------------

    private function projectDefinitions(): array
    {
        return [
            // ─── Project 1 ─── Selesai ───────────────────────────────────────
            [
                "customer" => "TNI Angkatan Udara",
                "contract_no" => "KSAD/PKS/01/2024",
                "aircraft_reg" => "A-2901",
                "aircraft_type" => "CN235-220",
                "description" =>
                    "Overhaul besar pesawat angkut militer CN235-220 milik TNI AU. Mencakup pengecekan struktur, sistem avionik, dan powerplant.",
                "progress" => 0.98,
                "start_date" => "2024-01-08",
                "finish_date" => "2024-05-31",
                "work_days" => 100,
                "phases" => [
                    [
                        "type" => "predock",
                        "no" => "A",
                        "name" => "Pre-Dock Inspection & Preparation",
                        "progress" => 1.0,
                        "allocation" => 0.1,
                        "start_date" => "2024-01-08",
                        "finish_date" => "2024-01-26",
                        "work_days" => 13,
                        "task_groups" => [
                            [
                                "no" => "A.1",
                                "name" => "Dokumentasi & Perencanaan",
                                "progress" => 1.0,
                                "allocation" => 0.05,
                                "start_date" => "2024-01-08",
                                "finish_date" => "2024-01-12",
                                "work_days" => 5,
                                "tasks" => [
                                    [
                                        "no" => "A.1.1",
                                        "name" => "Review maintenance manual",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-01-08",
                                        "finish_date" => "2024-01-09",
                                        "work_days" => 2,
                                    ],
                                    [
                                        "no" => "A.1.2",
                                        "name" => "Penyusunan WBS & jadwal",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-01-10",
                                        "finish_date" => "2024-01-11",
                                        "work_days" => 2,
                                    ],
                                    [
                                        "no" => "A.1.3",
                                        "name" => "Kick-off meeting",
                                        "progress" => 1.0,
                                        "allocation" => 0.01,
                                        "start_date" => "2024-01-12",
                                        "finish_date" => "2024-01-12",
                                        "work_days" => 1,
                                    ],
                                ],
                            ],
                            [
                                "no" => "A.2",
                                "name" => "Pre-Dock Inspection",
                                "progress" => 1.0,
                                "allocation" => 0.05,
                                "start_date" => "2024-01-15",
                                "finish_date" => "2024-01-26",
                                "work_days" => 8,
                                "tasks" => [
                                    [
                                        "no" => "A.2.1",
                                        "name" => "General visual inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-01-15",
                                        "finish_date" => "2024-01-17",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.2.2",
                                        "name" => "Fuel system check",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-01-18",
                                        "finish_date" => "2024-01-22",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.2.3",
                                        "name" => "Logbook & doc review",
                                        "progress" => 1.0,
                                        "allocation" => 0.01,
                                        "start_date" => "2024-01-23",
                                        "finish_date" => "2024-01-26",
                                        "work_days" => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "indock",
                        "no" => "B",
                        "name" => "In-Dock Maintenance & Overhaul",
                        "progress" => 0.98,
                        "allocation" => 0.8,
                        "start_date" => "2024-01-29",
                        "finish_date" => "2024-05-03",
                        "work_days" => 70,
                        "task_groups" => [
                            [
                                "no" => "B.1",
                                "name" => "Structural Inspection",
                                "progress" => 1.0,
                                "allocation" => 0.2,
                                "start_date" => "2024-01-29",
                                "finish_date" => "2024-02-23",
                                "work_days" => 20,
                                "tasks" => [
                                    [
                                        "no" => "B.1.1",
                                        "name" =>
                                            "Fuselage skin inspection (NDT)",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-01-29",
                                        "finish_date" => "2024-02-02",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.1.2",
                                        "name" => "Wing spar inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-02-05",
                                        "finish_date" => "2024-02-09",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.1.3",
                                        "name" => "Empennage inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.04,
                                        "start_date" => "2024-02-12",
                                        "finish_date" => "2024-02-16",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.1.4",
                                        "name" =>
                                            "Corrosion treatment & repair",
                                        "progress" => 1.0,
                                        "allocation" => 0.04,
                                        "start_date" => "2024-02-19",
                                        "finish_date" => "2024-02-23",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.2",
                                "name" => "Powerplant Overhaul",
                                "progress" => 1.0,
                                "allocation" => 0.2,
                                "start_date" => "2024-02-26",
                                "finish_date" => "2024-03-22",
                                "work_days" => 20,
                                "tasks" => [
                                    [
                                        "no" => "B.2.1",
                                        "name" =>
                                            "Engine removal & disassembly",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-02-26",
                                        "finish_date" => "2024-03-01",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.2.2",
                                        "name" => "Hot section inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-03-04",
                                        "finish_date" => "2024-03-08",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.2.3",
                                        "name" => "Propeller overhaul",
                                        "progress" => 1.0,
                                        "allocation" => 0.04,
                                        "start_date" => "2024-03-11",
                                        "finish_date" => "2024-03-15",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.2.4",
                                        "name" =>
                                            "Engine reassembly & installation",
                                        "progress" => 1.0,
                                        "allocation" => 0.04,
                                        "start_date" => "2024-03-18",
                                        "finish_date" => "2024-03-22",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.3",
                                "name" => "Avionics & Electrical",
                                "progress" => 0.95,
                                "allocation" => 0.2,
                                "start_date" => "2024-03-25",
                                "finish_date" => "2024-04-19",
                                "work_days" => 20,
                                "tasks" => [
                                    [
                                        "no" => "B.3.1",
                                        "name" => "Wiring harness inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.05,
                                        "start_date" => "2024-03-25",
                                        "finish_date" => "2024-03-29",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.3.2",
                                        "name" => "VHF/UHF radio overhaul",
                                        "progress" => 1.0,
                                        "allocation" => 0.05,
                                        "start_date" => "2024-04-01",
                                        "finish_date" => "2024-04-05",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.3.3",
                                        "name" =>
                                            "Navigation system calibration",
                                        "progress" => 0.9,
                                        "allocation" => 0.05,
                                        "start_date" => "2024-04-08",
                                        "finish_date" => "2024-04-12",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.3.4",
                                        "name" => "Autopilot system check",
                                        "progress" => 0.9,
                                        "allocation" => 0.05,
                                        "start_date" => "2024-04-15",
                                        "finish_date" => "2024-04-19",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.4",
                                "name" => "Landing Gear & Hydraulics",
                                "progress" => 1.0,
                                "allocation" => 0.2,
                                "start_date" => "2024-04-22",
                                "finish_date" => "2024-05-03",
                                "work_days" => 10,
                                "tasks" => [
                                    [
                                        "no" => "B.4.1",
                                        "name" => "Landing gear overhaul",
                                        "progress" => 1.0,
                                        "allocation" => 0.1,
                                        "start_date" => "2024-04-22",
                                        "finish_date" => "2024-04-26",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.4.2",
                                        "name" =>
                                            "Hydraulic system flush & test",
                                        "progress" => 1.0,
                                        "allocation" => 0.1,
                                        "start_date" => "2024-04-29",
                                        "finish_date" => "2024-05-03",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "postdock",
                        "no" => "C",
                        "name" => "Post-Dock Test & Delivery",
                        "progress" => 0.95,
                        "allocation" => 0.1,
                        "start_date" => "2024-05-06",
                        "finish_date" => "2024-05-31",
                        "work_days" => 17,
                        "task_groups" => [
                            [
                                "no" => "C.1",
                                "name" => "Ground Test",
                                "progress" => 1.0,
                                "allocation" => 0.05,
                                "start_date" => "2024-05-06",
                                "finish_date" => "2024-05-14",
                                "work_days" => 7,
                                "tasks" => [
                                    [
                                        "no" => "C.1.1",
                                        "name" => "Engine ground run test",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-05-06",
                                        "finish_date" => "2024-05-08",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "C.1.2",
                                        "name" => "Systems functional test",
                                        "progress" => 1.0,
                                        "allocation" => 0.03,
                                        "start_date" => "2024-05-09",
                                        "finish_date" => "2024-05-14",
                                        "work_days" => 4,
                                    ],
                                ],
                            ],
                            [
                                "no" => "C.2",
                                "name" => "Flight Test & Delivery",
                                "progress" => 0.9,
                                "allocation" => 0.05,
                                "start_date" => "2024-05-15",
                                "finish_date" => "2024-05-31",
                                "work_days" => 10,
                                "tasks" => [
                                    [
                                        "no" => "C.2.1",
                                        "name" => "Acceptance flight test",
                                        "progress" => 1.0,
                                        "allocation" => 0.03,
                                        "start_date" => "2024-05-15",
                                        "finish_date" => "2024-05-22",
                                        "work_days" => 6,
                                    ],
                                    [
                                        "no" => "C.2.2",
                                        "name" =>
                                            "Documentation & customer sign-off",
                                        "progress" => 0.8,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-05-23",
                                        "finish_date" => "2024-05-31",
                                        "work_days" => 4,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ─── Project 2 ─── Sedang berjalan ──────────────────────────────
            [
                "customer" => "Philippine Air Force",
                "contract_no" => "PAF/MRO/2024/009",
                "aircraft_reg" => "PAF-3402",
                "aircraft_type" => "C212-400",
                "description" =>
                    "Scheduled heavy maintenance C212-400 milik Philippine Air Force. Fokus pada structural repair dan avionics upgrade.",
                "progress" => 0.52,
                "start_date" => "2024-06-03",
                "finish_date" => "2024-11-29",
                "work_days" => 120,
                "phases" => [
                    [
                        "type" => "predock",
                        "no" => "A",
                        "name" => "Pre-Dock & Incoming Inspection",
                        "progress" => 1.0,
                        "allocation" => 0.1,
                        "start_date" => "2024-06-03",
                        "finish_date" => "2024-06-21",
                        "work_days" => 14,
                        "task_groups" => [
                            [
                                "no" => "A.1",
                                "name" => "Incoming Inspection",
                                "progress" => 1.0,
                                "allocation" => 0.06,
                                "start_date" => "2024-06-03",
                                "finish_date" => "2024-06-14",
                                "work_days" => 10,
                                "tasks" => [
                                    [
                                        "no" => "A.1.1",
                                        "name" =>
                                            "Cosmetic & general inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-06-03",
                                        "finish_date" => "2024-06-05",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.1.2",
                                        "name" => "Defect list compilation",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-06-06",
                                        "finish_date" => "2024-06-10",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.1.3",
                                        "name" => "Work scope finalization",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-06-11",
                                        "finish_date" => "2024-06-14",
                                        "work_days" => 4,
                                    ],
                                ],
                            ],
                            [
                                "no" => "A.2",
                                "name" => "Material Preparation",
                                "progress" => 1.0,
                                "allocation" => 0.04,
                                "start_date" => "2024-06-17",
                                "finish_date" => "2024-06-21",
                                "work_days" => 4,
                                "tasks" => [
                                    [
                                        "no" => "A.2.1",
                                        "name" =>
                                            "Parts requisition & ordering",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-06-17",
                                        "finish_date" => "2024-06-19",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.2.2",
                                        "name" => "Tooling & equipment check",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-06-20",
                                        "finish_date" => "2024-06-21",
                                        "work_days" => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "indock",
                        "no" => "B",
                        "name" => "In-Dock Heavy Maintenance",
                        "progress" => 0.48,
                        "allocation" => 0.8,
                        "start_date" => "2024-06-24",
                        "finish_date" => "2024-11-08",
                        "work_days" => 96,
                        "task_groups" => [
                            [
                                "no" => "B.1",
                                "name" => "Fuselage Structural Work",
                                "progress" => 1.0,
                                "allocation" => 0.2,
                                "start_date" => "2024-06-24",
                                "finish_date" => "2024-07-26",
                                "work_days" => 25,
                                "tasks" => [
                                    [
                                        "no" => "B.1.1",
                                        "name" => "Fuselage station inspection",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-06-24",
                                        "finish_date" => "2024-07-05",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.1.2",
                                        "name" => "Frame & longeron repair",
                                        "progress" => 1.0,
                                        "allocation" => 0.08,
                                        "start_date" => "2024-07-08",
                                        "finish_date" => "2024-07-19",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.1.3",
                                        "name" => "Skin panel replacement",
                                        "progress" => 1.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-07-22",
                                        "finish_date" => "2024-07-26",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.2",
                                "name" => "Powerplant & Propulsion",
                                "progress" => 0.9,
                                "allocation" => 0.2,
                                "start_date" => "2024-07-29",
                                "finish_date" => "2024-08-30",
                                "work_days" => 25,
                                "tasks" => [
                                    [
                                        "no" => "B.2.1",
                                        "name" => "TPE331 engine removal",
                                        "progress" => 1.0,
                                        "allocation" => 0.05,
                                        "start_date" => "2024-07-29",
                                        "finish_date" => "2024-08-02",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.2.2",
                                        "name" =>
                                            "Engine performance restoration",
                                        "progress" => 1.0,
                                        "allocation" => 0.08,
                                        "start_date" => "2024-08-05",
                                        "finish_date" => "2024-08-16",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.2.3",
                                        "name" =>
                                            "Engine reinstallation & test",
                                        "progress" => 0.7,
                                        "allocation" => 0.07,
                                        "start_date" => "2024-08-19",
                                        "finish_date" => "2024-08-30",
                                        "work_days" => 10,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.3",
                                "name" => "Avionics Upgrade",
                                "progress" => 0.2,
                                "allocation" => 0.2,
                                "start_date" => "2024-09-02",
                                "finish_date" => "2024-10-04",
                                "work_days" => 23,
                                "tasks" => [
                                    [
                                        "no" => "B.3.1",
                                        "name" => "Glass cockpit installation",
                                        "progress" => 0.5,
                                        "allocation" => 0.08,
                                        "start_date" => "2024-09-02",
                                        "finish_date" => "2024-09-13",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.3.2",
                                        "name" =>
                                            "ADS-B transponder installation",
                                        "progress" => 0.1,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-09-16",
                                        "finish_date" => "2024-09-27",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.3.3",
                                        "name" => "Wiring & integration test",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-09-30",
                                        "finish_date" => "2024-10-04",
                                        "work_days" => 3,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.4",
                                "name" => "Interior & Systems",
                                "progress" => 0.0,
                                "allocation" => 0.2,
                                "start_date" => "2024-10-07",
                                "finish_date" => "2024-11-08",
                                "work_days" => 23,
                                "tasks" => [
                                    [
                                        "no" => "B.4.1",
                                        "name" =>
                                            "Cabin interior refurbishment",
                                        "progress" => 0.0,
                                        "allocation" => 0.08,
                                        "start_date" => "2024-10-07",
                                        "finish_date" => "2024-10-18",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.4.2",
                                        "name" => "Oxygen & ECS system check",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-10-21",
                                        "finish_date" => "2024-11-01",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.4.3",
                                        "name" =>
                                            "Fire detection system overhaul",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2024-11-04",
                                        "finish_date" => "2024-11-08",
                                        "work_days" => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "postdock",
                        "no" => "C",
                        "name" => "Post-Dock Acceptance Test",
                        "progress" => 0.0,
                        "allocation" => 0.1,
                        "start_date" => "2024-11-11",
                        "finish_date" => "2024-11-29",
                        "work_days" => 10,
                        "task_groups" => [
                            [
                                "no" => "C.1",
                                "name" => "Ground Run & Functional Test",
                                "progress" => 0.0,
                                "allocation" => 0.05,
                                "start_date" => "2024-11-11",
                                "finish_date" => "2024-11-19",
                                "work_days" => 5,
                                "tasks" => [
                                    [
                                        "no" => "C.1.1",
                                        "name" => "Engine ground run",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-11-11",
                                        "finish_date" => "2024-11-13",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "C.1.2",
                                        "name" => "Full systems check",
                                        "progress" => 0.0,
                                        "allocation" => 0.03,
                                        "start_date" => "2024-11-14",
                                        "finish_date" => "2024-11-19",
                                        "work_days" => 2,
                                    ],
                                ],
                            ],
                            [
                                "no" => "C.2",
                                "name" => "Flight Test & Handover",
                                "progress" => 0.0,
                                "allocation" => 0.05,
                                "start_date" => "2024-11-20",
                                "finish_date" => "2024-11-29",
                                "work_days" => 5,
                                "tasks" => [
                                    [
                                        "no" => "C.2.1",
                                        "name" => "Test flight (1.5 hr)",
                                        "progress" => 0.0,
                                        "allocation" => 0.03,
                                        "start_date" => "2024-11-20",
                                        "finish_date" => "2024-11-22",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "C.2.2",
                                        "name" => "Punch list & final handover",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2024-11-25",
                                        "finish_date" => "2024-11-29",
                                        "work_days" => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // ─── Project 3 ─── Baru mulai ────────────────────────────────────
            [
                "customer" => "Senegal Air Force",
                "contract_no" => "SAF/CN235/2025/001",
                "aircraft_reg" => "SAF-0102",
                "aircraft_type" => "CN235-220M",
                "description" =>
                    "Medium maintenance CN235-220M maritim patrol aircraft milik Senegal. Fokus pada sensor systems dan structural inspection.",
                "progress" => 0.12,
                "start_date" => "2025-01-06",
                "finish_date" => "2025-07-25",
                "work_days" => 140,
                "phases" => [
                    [
                        "type" => "predock",
                        "no" => "A",
                        "name" => "Pre-Dock & Planning",
                        "progress" => 1.0,
                        "allocation" => 0.08,
                        "start_date" => "2025-01-06",
                        "finish_date" => "2025-01-24",
                        "work_days" => 14,
                        "task_groups" => [
                            [
                                "no" => "A.1",
                                "name" => "Initial Assessment",
                                "progress" => 1.0,
                                "allocation" => 0.04,
                                "start_date" => "2025-01-06",
                                "finish_date" => "2025-01-14",
                                "work_days" => 7,
                                "tasks" => [
                                    [
                                        "no" => "A.1.1",
                                        "name" => "Aircraft logbook review",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-01-06",
                                        "finish_date" => "2025-01-08",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "A.1.2",
                                        "name" => "Initial visual assessment",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-01-09",
                                        "finish_date" => "2025-01-14",
                                        "work_days" => 4,
                                    ],
                                ],
                            ],
                            [
                                "no" => "A.2",
                                "name" => "Work Order & Material Planning",
                                "progress" => 1.0,
                                "allocation" => 0.04,
                                "start_date" => "2025-01-15",
                                "finish_date" => "2025-01-24",
                                "work_days" => 7,
                                "tasks" => [
                                    [
                                        "no" => "A.2.1",
                                        "name" => "Work order preparation",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-01-15",
                                        "finish_date" => "2025-01-20",
                                        "work_days" => 4,
                                    ],
                                    [
                                        "no" => "A.2.2",
                                        "name" =>
                                            "Material & spare part ordering",
                                        "progress" => 1.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-01-21",
                                        "finish_date" => "2025-01-24",
                                        "work_days" => 3,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "indock",
                        "no" => "B",
                        "name" => "In-Dock Maintenance",
                        "progress" => 0.07,
                        "allocation" => 0.84,
                        "start_date" => "2025-01-27",
                        "finish_date" => "2025-07-04",
                        "work_days" => 115,
                        "task_groups" => [
                            [
                                "no" => "B.1",
                                "name" => "Structural Inspection",
                                "progress" => 0.3,
                                "allocation" => 0.2,
                                "start_date" => "2025-01-27",
                                "finish_date" => "2025-03-21",
                                "work_days" => 40,
                                "tasks" => [
                                    [
                                        "no" => "B.1.1",
                                        "name" => "Wing structure inspection",
                                        "progress" => 0.7,
                                        "allocation" => 0.08,
                                        "start_date" => "2025-01-27",
                                        "finish_date" => "2025-02-21",
                                        "work_days" => 20,
                                    ],
                                    [
                                        "no" => "B.1.2",
                                        "name" => "Fuselage frame inspection",
                                        "progress" => 0.1,
                                        "allocation" => 0.08,
                                        "start_date" => "2025-02-24",
                                        "finish_date" => "2025-03-14",
                                        "work_days" => 15,
                                    ],
                                    [
                                        "no" => "B.1.3",
                                        "name" => "Corrosion control treatment",
                                        "progress" => 0.0,
                                        "allocation" => 0.04,
                                        "start_date" => "2025-03-17",
                                        "finish_date" => "2025-03-21",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.2",
                                "name" => "Maritime Sensor Systems",
                                "progress" => 0.0,
                                "allocation" => 0.2,
                                "start_date" => "2025-03-24",
                                "finish_date" => "2025-05-09",
                                "work_days" => 35,
                                "tasks" => [
                                    [
                                        "no" => "B.2.1",
                                        "name" => "Search radar overhaul",
                                        "progress" => 0.0,
                                        "allocation" => 0.08,
                                        "start_date" => "2025-03-24",
                                        "finish_date" => "2025-04-18",
                                        "work_days" => 20,
                                    ],
                                    [
                                        "no" => "B.2.2",
                                        "name" => "FLIR sensor inspection",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2025-04-21",
                                        "finish_date" => "2025-05-02",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.2.3",
                                        "name" => "Mission computer update",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2025-05-05",
                                        "finish_date" => "2025-05-09",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.3",
                                "name" => "Engine & Propulsion",
                                "progress" => 0.0,
                                "allocation" => 0.2,
                                "start_date" => "2025-05-12",
                                "finish_date" => "2025-06-20",
                                "work_days" => 30,
                                "tasks" => [
                                    [
                                        "no" => "B.3.1",
                                        "name" => "CT7-9C engine borescope",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2025-05-12",
                                        "finish_date" => "2025-05-23",
                                        "work_days" => 10,
                                    ],
                                    [
                                        "no" => "B.3.2",
                                        "name" => "Engine accessories overhaul",
                                        "progress" => 0.0,
                                        "allocation" => 0.08,
                                        "start_date" => "2025-05-26",
                                        "finish_date" => "2025-06-13",
                                        "work_days" => 15,
                                    ],
                                    [
                                        "no" => "B.3.3",
                                        "name" => "Propeller blade inspection",
                                        "progress" => 0.0,
                                        "allocation" => 0.06,
                                        "start_date" => "2025-06-16",
                                        "finish_date" => "2025-06-20",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                            [
                                "no" => "B.4",
                                "name" => "Fuel & Environmental Systems",
                                "progress" => 0.0,
                                "allocation" => 0.24,
                                "start_date" => "2025-06-23",
                                "finish_date" => "2025-07-04",
                                "work_days" => 10,
                                "tasks" => [
                                    [
                                        "no" => "B.4.1",
                                        "name" =>
                                            "Fuel tank inspection & sealing",
                                        "progress" => 0.0,
                                        "allocation" => 0.12,
                                        "start_date" => "2025-06-23",
                                        "finish_date" => "2025-06-27",
                                        "work_days" => 5,
                                    ],
                                    [
                                        "no" => "B.4.2",
                                        "name" =>
                                            "Air conditioning system overhaul",
                                        "progress" => 0.0,
                                        "allocation" => 0.12,
                                        "start_date" => "2025-06-30",
                                        "finish_date" => "2025-07-04",
                                        "work_days" => 5,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        "type" => "postdock",
                        "no" => "C",
                        "name" => "Post-Dock Verification & Handover",
                        "progress" => 0.0,
                        "allocation" => 0.08,
                        "start_date" => "2025-07-07",
                        "finish_date" => "2025-07-25",
                        "work_days" => 11,
                        "task_groups" => [
                            [
                                "no" => "C.1",
                                "name" => "Systems Verification",
                                "progress" => 0.0,
                                "allocation" => 0.04,
                                "start_date" => "2025-07-07",
                                "finish_date" => "2025-07-15",
                                "work_days" => 7,
                                "tasks" => [
                                    [
                                        "no" => "C.1.1",
                                        "name" =>
                                            "Avionics system verification",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-07-07",
                                        "finish_date" => "2025-07-10",
                                        "work_days" => 4,
                                    ],
                                    [
                                        "no" => "C.1.2",
                                        "name" => "Engine ground run",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-07-11",
                                        "finish_date" => "2025-07-15",
                                        "work_days" => 3,
                                    ],
                                ],
                            ],
                            [
                                "no" => "C.2",
                                "name" => "Acceptance & Delivery",
                                "progress" => 0.0,
                                "allocation" => 0.04,
                                "start_date" => "2025-07-16",
                                "finish_date" => "2025-07-25",
                                "work_days" => 4,
                                "tasks" => [
                                    [
                                        "no" => "C.2.1",
                                        "name" =>
                                            "Acceptance flight (maritime profile)",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-07-16",
                                        "finish_date" => "2025-07-21",
                                        "work_days" => 3,
                                    ],
                                    [
                                        "no" => "C.2.2",
                                        "name" =>
                                            "Documentation & ferry preparation",
                                        "progress" => 0.0,
                                        "allocation" => 0.02,
                                        "start_date" => "2025-07-22",
                                        "finish_date" => "2025-07-25",
                                        "work_days" => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
