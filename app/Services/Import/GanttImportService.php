<?php

namespace App\Services\Import;

use App\Models\DockPhase;
use App\Models\MwsPart;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GanttImportService
{
    // Kolom index di sheet "Reporting" (0-based)
    const COL_CUSTOMER = 0;
    const COL_CONTRACT = 1;
    const COL_SN = 2;
    const COL_NO = 3;
    const COL_TASK = 4;
    const COL_PROGRESS = 5;
    const COL_ALLOCATION = 6;
    const COL_TOTAL = 7;
    const COL_START = 8;
    const COL_FINISH = 9;
    const COL_WORKDAY = 10;
    const COL_ROW_LEVEL = 11;

    // Row index header info (0-based)
    const ROW_CUSTOMER_INFO = 8; // row 9 di Excel

    // Row mulai data (0-based)
    const ROW_DATA_START = 9; // row 10 di Excel

    public function import(string $filePath): Project
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Reporting');

        if (!$sheet) {
            throw new \RuntimeException('Sheet "Reporting" tidak ditemukan di file Excel.');
        }

        $rows = $sheet->toArray(null, true, true, false);

        return DB::transaction(function () use ($rows) {
            $project = $this->createProject($rows);
            $dockPhases = $this->createDockPhases($rows, $project);
            $this->createTaskGroupsAndBelow($rows, $dockPhases);

            return $project;
        });
    }

    // -------------------------------------------------------------------------
    // Level 1 — Project
    // -------------------------------------------------------------------------

    private function createProject(array $rows): Project
    {
        $infoRow = $rows[self::ROW_CUSTOMER_INFO] ?? [];
        $level1 = $this->findFirstRowByLevel($rows, 'Level 1');

        $customer = $this->val($infoRow, self::COL_CUSTOMER);
        $contractNo = $this->val($infoRow, self::COL_CONTRACT);
        $aircraftSn = $this->val($infoRow, self::COL_SN);

        // Parse aircraft type & reg dari S/N, e.g. "TN 811"
        $aircraftReg = $aircraftSn;
        $aircraftType = null;

        if ($level1) {
            $description = $this->val($level1, self::COL_TASK);
            $progress = $this->floatVal($level1, self::COL_TOTAL);
            $startDate = $this->dateVal($level1, self::COL_START);
            $finishDate = $this->dateVal($level1, self::COL_FINISH);
            $workDays = $this->intVal($level1, self::COL_WORKDAY);

            // Coba ekstrak aircraft type dari deskripsi, e.g. "CN235-110"
            if (preg_match('/CN\d+[-\s]\d+/i', $description ?? '', $m)) {
                $aircraftType = $m[0];
            }
        }

        return Project::create([
            'customer' => $customer,
            'contract_no' => $contractNo,
            'aircraft_reg' => $aircraftReg,
            'aircraft_type' => $aircraftType,
            'description' => $description ?? null,
            'progress' => $progress ?? 0,
            'start_date' => $startDate ?? null,
            'finish_date' => $finishDate ?? null,
            'work_days' => $workDays ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Level 2 — Dock Phases
    // -------------------------------------------------------------------------

    private function createDockPhases(array $rows, Project $project): array
    {
        $phases = [];

        foreach ($rows as $index => $row) {
            if ($index < self::ROW_DATA_START) {
                continue;
            }

            $level = $this->val($row, self::COL_ROW_LEVEL);

            if ($level !== 'Level 2') {
                continue;
            }

            $no = trim((string) $this->val($row, self::COL_NO));
            $taskName = trim((string) $this->val($row, self::COL_TASK));
            $type = $this->classifyDockPhase($no, $taskName);

            if ($type === 'unknown') {
                Log::warning("GanttImport: Row {$index} Level 2 tidak dikenali.", [
                    'no' => $no,
                    'task' => $taskName,
                ]);
                continue;
            }

            $phase = DockPhase::create([
                'project_id' => $project->id,
                'type' => $type,
                'no' => $no,
                'name' => $taskName,
                'progress' => $this->floatVal($row, self::COL_PROGRESS),
                'allocation' => $this->floatVal($row, self::COL_ALLOCATION),
                'start_date' => $this->dateVal($row, self::COL_START),
                'finish_date' => $this->dateVal($row, self::COL_FINISH),
                'work_days' => $this->intVal($row, self::COL_WORKDAY),
            ]);

            // Key by "no" (A, B, C) untuk lookup cepat
            $phases[$no] = $phase;
        }

        return $phases;
    }

    private function classifyDockPhase(string $no, string $taskName): string
    {
        // Cek dari NO dulu (A, B, C)
        $prefix = strtoupper($no);
        if ($prefix === 'A')
            return 'predock';
        if ($prefix === 'B')
            return 'indock';
        if ($prefix === 'C')
            return 'postdock';

        // Fallback cek dari nama task
        $task = strtoupper($taskName);
        if (str_contains($task, 'PRE DOCK') || str_contains($task, 'PREDOCK'))
            return 'predock';
        if (str_contains($task, 'IN DOCK') || str_contains($task, 'INDOCK'))
            return 'indock';
        if (str_contains($task, 'POST DOCK') || str_contains($task, 'POSTDOCK'))
            return 'postdock';

        return 'unknown';
    }

    // -------------------------------------------------------------------------
    // Level 3, 4, 5 — Task Groups, Tasks, MWS Parts
    // -------------------------------------------------------------------------

    private function createTaskGroupsAndBelow(array $rows, array $dockPhases): void
    {
        $currentDockPhase = null; // DockPhase aktif
        $currentTaskGroup = null; // TaskGroup aktif
        $currentTask = null; // Task aktif

        $level1Row = $this->findFirstRowByLevel($rows, 'Level 1');
        $level1Title = $level1Row
            ? trim((string) $this->val($level1Row, self::COL_TASK))
            : null;

        $lastPart = MwsPart::orderBy('id', 'desc')->first();
        $partCounter = 1;
        if ($lastPart && preg_match('/(\d+)$/', $lastPart->part_id, $match)) {
            $partCounter = (int) $match[1] + 1;
        }

        foreach ($rows as $index => $row) {
            if ($index < self::ROW_DATA_START) {
                continue;
            }

            $level = $this->val($row, self::COL_ROW_LEVEL);

            if (!$level || !str_starts_with($level, 'Level')) {
                continue;
            }

            $no = trim((string) $this->val($row, self::COL_NO));
            $taskName = trim((string) $this->val($row, self::COL_TASK));

            switch ($level) {
                case 'Level 2':
                    $currentDockPhase = $dockPhases[$no] ?? null;
                    $currentTaskGroup = null;
                    $currentTask = null;
                    break;

                case 'Level 3':
                    if (!$currentDockPhase) {
                        break;
                    }

                    $currentTaskGroup = TaskGroup::create([
                        'dock_phase_id' => $currentDockPhase->id,
                        'no' => $no ?: null,
                        'name' => $taskName,
                        'progress' => $this->floatVal($row, self::COL_PROGRESS),
                        'allocation' => $this->floatVal($row, self::COL_ALLOCATION),
                        'start_date' => $this->dateVal($row, self::COL_START),
                        'finish_date' => $this->dateVal($row, self::COL_FINISH),
                        'work_days' => $this->intVal($row, self::COL_WORKDAY),
                    ]);
                    $currentTask = null;
                    break;

                case 'Level 4':
                    if (!$currentTaskGroup) {
                        break;
                    }

                    $currentTask = Task::create([
                        'task_group_id' => $currentTaskGroup->id,
                        'no' => $no ?: null,
                        'name' => $taskName,
                        'progress' => $this->floatVal($row, self::COL_PROGRESS),
                        'allocation' => $this->floatVal($row, self::COL_ALLOCATION),
                        'start_date' => $this->dateVal($row, self::COL_START),
                        'finish_date' => $this->dateVal($row, self::COL_FINISH),
                        'work_days' => $this->intVal($row, self::COL_WORKDAY),
                    ]);
                    break;

                case 'Level 5':
                    if (!$currentTask) {
                        break;
                    }

                    // Level 5 = MWS Part
                    $partId = 'MWS-' . str_pad($partCounter, 3, '0', STR_PAD_LEFT);
                    $partCounter++;

                    MwsPart::create([
                        'task_id' => $currentTask->id,
                        'part_id' => $partId,
                        'title' => $taskName,
                        'progress' => $this->floatVal($row, self::COL_PROGRESS),
                        'start_date' => $this->dateVal($row, self::COL_START),
                        'finish_date' => $this->dateVal($row, self::COL_FINISH),
                        'status' => 'open',
                    ]);
                    break;
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function findFirstRowByLevel(array $rows, string $level): ?array
    {
        foreach ($rows as $index => $row) {
            if ($index < self::ROW_DATA_START) {
                continue;
            }
            if ($this->val($row, self::COL_ROW_LEVEL) === $level) {
                return $row;
            }
        }

        return null;
    }

    private function val(array $row, int $col): mixed
    {
        $v = $row[$col] ?? null;
        return ($v === '' || $v === null) ? null : $v;
    }

    private function floatVal(array $row, int $col): float
    {
        $v = $this->val($row, $col);
        return is_numeric($v) ? (float) $v : 0.0;
    }

    private function intVal(array $row, int $col): ?int
    {
        $v = $this->val($row, $col);
        return is_numeric($v) ? (int) $v : null;
    }

    private function dateVal(array $row, int $col): ?string
    {
        $v = $this->val($row, $col);

        if (!$v)
            return null;

        try {
            if ($v instanceof \DateTimeInterface) {
                return Carbon::instance($v)->toDateString();
            }
            return Carbon::parse($v)->toDateString();
        } catch (\Exception) {
            return null;
        }
    }

    private function sanitizePartId(string $name): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9\-\/\.]/', '', $name));
    }
}