<?php

namespace App\Services\Export;

use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProjectExportService
{
    public function export(Project $project): Spreadsheet
    {
        // Load relationships fully
        $project->load([
            'dockPhases' => function ($q) {
                $q->orderByRaw("CASE type 
                    WHEN 'predock'  THEN 1 
                    WHEN 'indock'   THEN 2 
                    WHEN 'postdock' THEN 3 
                    ELSE 4 END");
            },
            'dockPhases.taskGroups' => fn($q) => $q->orderBy('no'),
            'dockPhases.taskGroups.tasks' => fn($q) => $q->orderBy('no'),
            'dockPhases.taskGroups.tasks.mwsParts.steps' => fn($q) => $q->orderBy('no'),
            'dockPhases.taskGroups.tasks.mwsParts.steps.subSteps',
            'dockPhases.taskGroups.tasks.mwsParts.consumables',
        ]);

        $spreadsheet = new Spreadsheet();
        
        // Setup Sheet 1: Project Hierarchy
        $this->buildHierarchySheet($spreadsheet->getActiveSheet(), $project);
        
        // Setup Sheet 2: MWS Procedures & Steps
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('MWS Steps');
        $this->buildStepsSheet($sheet2, $project);
        
        // Setup Sheet 3: Consumables
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Consumables');
        $this->buildConsumablesSheet($sheet3, $project);
        
        // Set active sheet to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildHierarchySheet($sheet, Project $project): void
    {
        $sheet->setTitle('Project Hierarchy');
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(18); // Level
        $sheet->getColumnDimension('B')->setWidth(15); // Structure / No
        $sheet->getColumnDimension('C')->setWidth(50); // Title / Name
        $sheet->getColumnDimension('D')->setWidth(14); // Progress (%)
        $sheet->getColumnDimension('E')->setWidth(14); // Allocation
        $sheet->getColumnDimension('F')->setWidth(14); // Start Date
        $sheet->getColumnDimension('G')->setWidth(14); // Finish Date
        $sheet->getColumnDimension('H')->setWidth(14); // Work Days
        $sheet->getColumnDimension('I')->setWidth(15); // Status
        $sheet->getColumnDimension('J')->setWidth(16); // Total Man Hours
        $sheet->getColumnDimension('K')->setWidth(14); // Men Power

        // Title Block
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'PROJECT MASTER WORK STRUCTURE (MWS) REPORT');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Metadata Block
        $sheet->setCellValue('A3', 'Customer:');
        $sheet->setCellValue('B3', $project->customer);
        $sheet->getStyle('A3')->getFont()->setBold(true);
        
        $sheet->setCellValue('A4', 'Aircraft type:');
        $sheet->setCellValue('B4', $project->aircraft_type);
        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->setCellValue('A5', 'Aircraft Reg:');
        $sheet->setCellValue('B5', $project->aircraft_reg ?? '—');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('D3', 'Contract No:');
        $sheet->setCellValue('E3', $project->contract_no ?? '—');
        $sheet->getStyle('D3')->getFont()->setBold(true);

        $sheet->setCellValue('D4', 'Period:');
        $sheet->setCellValue('E4', ($project->start_date ? $project->start_date->format('Y-m-d') : '—') . ' to ' . ($project->finish_date ? $project->finish_date->format('Y-m-d') : '—'));
        $sheet->getStyle('D4')->getFont()->setBold(true);

        $sheet->setCellValue('D5', 'Work Days:');
        $sheet->setCellValue('E5', $project->work_days ?? '—');
        $sheet->getStyle('D5')->getFont()->setBold(true);

        $sheet->setCellValue('G3', 'Overall Progress:');
        $sheet->setCellValue('H3', (float) $project->progress);
        $sheet->getStyle('G3')->getFont()->setBold(true);
        $sheet->getStyle('H3')->getNumberFormat()->setFormatCode('0.0%');

        // Table Header
        $headers = [
            'Row Level', 'Structure / No', 'Title / Name', 'Progress (%)',
            'Allocation', 'Start Date', 'Finish Date', 'Work Days',
            'Status', 'Total Man Hours', 'Men Power'
        ];
        
        $headerRow = 8;
        foreach ($headers as $colIndex => $header) {
            $colLetter = chr(65 + $colIndex); // A, B, C...
            $sheet->setCellValue($colLetter . $headerRow, $header);
        }

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];
        $sheet->getStyle('A8:K8')->applyFromArray($headerStyle);
        $sheet->getRowDimension(8)->setRowHeight(25);

        // Populate Hierarchy
        $row = 9;
        
        // Level 1: Project
        $this->writeHierarchyRow($sheet, $row++, 'Level 1 (Project)', '1', $project->description ?? $project->customer, $project->progress, null, $project->start_date, $project->finish_date, $project->work_days, null, null, null, 'level1');

        foreach ($project->dockPhases as $phase) {
            // Level 2: Dock Phase
            $this->writeHierarchyRow($sheet, $row++, 'Level 2 (Dock Phase)', $phase->no, $phase->name, $phase->progress, $phase->allocation, $phase->start_date, $phase->finish_date, $phase->work_days, null, null, null, 'level2');

            foreach ($phase->taskGroups as $group) {
                // Level 3: Task Group
                $this->writeHierarchyRow($sheet, $row++, 'Level 3 (Task Group)', $group->no, $group->name, $group->progress, $group->allocation, $group->start_date, $group->finish_date, $group->work_days, null, null, null, 'level3');

                foreach ($group->tasks as $task) {
                    // Level 4: Task
                    $this->writeHierarchyRow($sheet, $row++, 'Level 4 (Task)', $task->no, $task->name, $task->progress, $task->allocation, $task->start_date, $task->finish_date, $task->work_days, null, null, null, 'level4');

                    foreach ($task->mwsParts as $mws) {
                        // Level 5: MWS Part
                        $this->writeHierarchyRow($sheet, $row++, 'Level 5 (MWS Part)', $mws->part_id, $mws->title, $mws->progress, null, $mws->start_date, $mws->finish_date, $mws->ecd_finish_workdays, $mws->status, $mws->man_hours, $mws->men_powers, 'level5');
                    }
                }
            }
        }

        // Apply gridlines
        $sheet->setShowGridlines(true);
    }

    private function writeHierarchyRow($sheet, $row, $level, $no, $name, $progress, $allocation, $start, $finish, $workdays, $status, $manHours, $menPowers, $type): void
    {
        $sheet->setCellValue('A' . $row, $level);
        $sheet->setCellValue('B' . $row, $no);
        $sheet->setCellValue('C' . $row, $name);
        
        $sheet->setCellValue('D' . $row, $progress !== null ? (float) $progress : '');
        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('0.0%');
        
        $sheet->setCellValue('E' . $row, $allocation !== null ? (float) $allocation : '');
        if ($allocation !== null) {
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->setCellValue('F' . $row, $start ? $start->format('Y-m-d') : '');
        $sheet->setCellValue('G' . $row, $finish ? $finish->format('Y-m-d') : '');
        
        $sheet->setCellValue('H' . $row, $workdays);
        $sheet->setCellValue('I' . $row, $status);
        $sheet->setCellValue('J' . $row, $manHours);
        $sheet->setCellValue('K' . $row, $menPowers);

        // Apply alignment & borders
        $alignCenterCols = ['A', 'B', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        foreach ($alignCenterCols as $col) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Styling based on level type
        $style = [];
        if ($type === 'level1') {
            $style = [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']], // Light Blue
            ];
        } elseif ($type === 'level2') {
            $style = [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']], // Light Green
            ];
        } elseif ($type === 'level3') {
            $style = [
                'font' => ['bold' => true, 'size' => 9.5],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']], // Light Yellow
            ];
        } elseif ($type === 'level4') {
            $style = [
                'font' => ['bold' => false, 'size' => 9.5],
            ];
        } elseif ($type === 'level5') {
            $style = [
                'font' => ['italic' => true, 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F2F2']], // Light Gray
            ];
        }

        $style['borders'] = [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']]
        ];

        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($style);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    private function buildStepsSheet($sheet, Project $project): void
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15); // MWS ID
        $sheet->getColumnDimension('B')->setWidth(25); // MWS Title
        $sheet->getColumnDimension('C')->setWidth(18); // Part No
        $sheet->getColumnDimension('D')->setWidth(10); // Step No
        $sheet->getColumnDimension('E')->setWidth(65); // Step Description
        $sheet->getColumnDimension('F')->setWidth(12); // Plan Man
        $sheet->getColumnDimension('G')->setWidth(12); // Plan Hours
        $sheet->getColumnDimension('H')->setWidth(30); // Actual Mechanics
        $sheet->getColumnDimension('I')->setWidth(14); // Actual Hours
        $sheet->getColumnDimension('J')->setWidth(18); // Tech Sign-off
        $sheet->getColumnDimension('K')->setWidth(18); // Insp Sign-off
        $sheet->getColumnDimension('L')->setWidth(14); // Step Status

        // Title Block
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'MWS PROCEDURES & STEPS DETAILS');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Header
        $headers = [
            'MWS ID', 'MWS Title', 'Part Number', 'Step No', 'Step Description',
            'Plan Man', 'Plan Hours', 'Actual Mechanics', 'Actual Hours',
            'Tech Sign-off', 'Insp Sign-off', 'Step Status'
        ];
        
        $headerRow = 3;
        foreach ($headers as $colIndex => $header) {
            $colLetter = chr(65 + $colIndex);
            $sheet->setCellValue($colLetter . $headerRow, $header);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2F5597']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];
        $sheet->getStyle('A3:L3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Populate steps
        $row = 4;
        foreach ($project->dockPhases as $phase) {
            foreach ($phase->taskGroups as $group) {
                foreach ($group->tasks as $task) {
                    foreach ($task->mwsParts as $mws) {
                        foreach ($mws->steps->sortBy('no') as $step) {
                            $this->writeStepRow($sheet, $row++, $mws, $step);
                        }
                    }
                }
            }
        }

        // Auto wrap for step descriptions
        $sheet->getStyle('E4:E' . ($row - 1))->getAlignment()->setWrapText(true);
        $sheet->getStyle('H4:H' . ($row - 1))->getAlignment()->setWrapText(true);
        $sheet->setShowGridlines(true);
    }

    private function writeStepRow($sheet, $row, $mws, $step): void
    {
        $sheet->setCellValue('A' . $row, $mws->part_id);
        $sheet->setCellValue('B' . $row, $mws->title);
        $sheet->setCellValue('C' . $row, $mws->part_number);
        $sheet->setCellValue('D' . $row, 'Step ' . $step->no);

        // Compile detailed description
        $desc = $step->description;
        if ($step->caution) {
            $desc .= "\n[CAUTION: " . $step->caution . "]";
        }
        if ($step->note) {
            $desc .= "\n[NOTE: " . $step->note . "]";
        }
        if (!empty($step->details)) {
            $desc .= "\nDetails:\n" . implode("\n", array_map(fn($d) => "• " . $d, $step->details));
        }
        if ($step->subSteps && $step->subSteps->isNotEmpty()) {
            $desc .= "\nSub-steps:\n" . implode("\n", $step->subSteps->map(fn($s) => "  " . $s->label . ". " . $s->description)->toArray());
        }
        $sheet->setCellValue('E' . $row, $desc);

        $sheet->setCellValue('F' . $row, $step->plan_man);
        $sheet->setCellValue('G' . $row, $step->plan_hours);

        // Format actual mechanics
        $mechanics = $step->mechanics ?? collect();
        $mechList = $mechanics->map(fn($m) => $m->name . " (" . $m->nik . ")")->implode("\n");
        $sheet->setCellValue('H' . $row, $mechList);

        $sheet->setCellValue('I' . $row, $step->hours);

        // Tech Sign-off
        $techVal = $step->tech;
        if ($techVal === 'Approved') {
            $techVal = 'APPROVED';
            if ($mechanics->isNotEmpty()) {
                $techVal .= ' (by ' . $mechanics->first()->name . ')';
            }
        }
        $sheet->setCellValue('J' . $row, $techVal ?? '—');

        // Insp Sign-off
        $inspVal = $step->insp;
        if ($step->status === 'completed') {
            $inspVal = 'INSPECTED';
            if ($step->status_s_us) {
                $inspVal .= ' [' . $step->status_s_us . ']';
            }
        }
        $sheet->setCellValue('K' . $row, $inspVal ?? '—');

        $sheet->setCellValue('L' . $row, ucfirst($step->status));

        // Alignment center for most columns
        $centerCols = ['A', 'C', 'D', 'F', 'G', 'I', 'J', 'K', 'L'];
        foreach ($centerCols as $col) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Row height & border styling
        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]
            ]
        ];
        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($borderStyle);
        
        // Dynamically calculate row height based on step description lines
        $lineCount = substr_count($desc, "\n") + 1;
        $mechLineCount = substr_count($mechList, "\n") + 1;
        $maxLines = max($lineCount, $mechLineCount, 1);
        $sheet->getRowDimension($row)->setRowHeight(max(24, $maxLines * 15));
    }

    private function buildConsumablesSheet($sheet, Project $project): void
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(18); // MWS ID
        $sheet->getColumnDimension('B')->setWidth(30); // MWS Title
        $sheet->getColumnDimension('C')->setWidth(35); // Consumable Name
        $sheet->getColumnDimension('D')->setWidth(40); // Identification / References
        $sheet->getColumnDimension('E')->setWidth(16); // Quantity

        // Title Block
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'PROJECT CONSUMABLES LIST');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Header
        $headers = ['MWS ID', 'MWS Title', 'Consumable Name', 'Identification / References', 'Quantity'];
        $headerRow = 3;
        foreach ($headers as $colIndex => $header) {
            $colLetter = chr(65 + $colIndex);
            $sheet->setCellValue($colLetter . $headerRow, $header);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '595959']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
            ]
        ];
        $sheet->getStyle('A3:E3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(25);

        $row = 4;
        foreach ($project->dockPhases as $phase) {
            foreach ($phase->taskGroups as $group) {
                foreach ($group->tasks as $task) {
                    foreach ($task->mwsParts as $mws) {
                        foreach ($mws->consumables as $consumable) {
                            $sheet->setCellValue('A' . $row, $mws->part_id);
                            $sheet->setCellValue('B' . $row, $mws->title);
                            $sheet->setCellValue('C' . $row, $consumable->name);
                            $sheet->setCellValue('D' . $row, $consumable->identification ?? '—');
                            $sheet->setCellValue('E' . $row, $consumable->quantity);

                            // Center alignment for code and quantity
                            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            
                            $borderStyle = [
                                'borders' => [
                                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]
                                ]
                            ];
                            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($borderStyle);
                            $sheet->getRowDimension($row)->setRowHeight(20);
                            $row++;
                        }
                    }
                }
            }
        }

        $sheet->setShowGridlines(true);
    }
}
