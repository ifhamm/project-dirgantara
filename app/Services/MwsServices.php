<?php

namespace App\Services;

use Carbon\Carbon;

class MwsServices
{
    public static function calculateProgress($mwsPart)
    {
        $total = $mwsPart->steps->count();

        if ($total == 0) return 0;

        $value = 0;

        foreach ($mwsPart->steps as $step) {
            if ($step->status == 'completed') {
                $value += 1;
            } elseif (in_array($step->status, ['in_progress', 'approved'])) {
                $value += 0.5;
            }
        }

        return intval(($value / $total) * 100);
    }

    public static function calculateDuration($mwsPart)
    {
        $totalMinutes = 0;

        foreach ($mwsPart->steps as $step) {
            if ($step->hours) {
                $parts = explode(':', $step->hours);
                if (count($parts) === 2) {
                    $totalMinutes += ((int) $parts[0] * 60) + (int) $parts[1];
                }
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public static function workingDays($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $days = 0;

        while ($start->lt($end)) {
            $start->addDay();

            if ($start->isWeekday()) {
                $days++;
            }
        }

        return $days;
    }

    public static function calculateMenPowers($mwsPart)
    {
        $unique = [];

        foreach ($mwsPart->steps as $step) {
            if ($step->tech) {
                $techs = explode(',', $step->tech);
                $unique = array_merge($unique, $techs);
            }
        }

        $mwsPart->men_powers = count(array_unique($unique));
        $mwsPart->save();
    }

    public static function updateSchedule($mwsPart)
    {
        $jobDays = [
            'Repair' => 60,
            'Overhaul' => 80,
            'F.Test' => 3,
        ];

        $workdays = $jobDays[$mwsPart->job_type] ?? null;

        if (!$mwsPart->start_date || !$workdays) return;

        $date = Carbon::parse($mwsPart->start_date);
        $added = 0;

        while ($added < $workdays) {
            $date->addDay();
            if ($date->isWeekday()) {
                $added++;
            }
        }

        $mwsPart->schedule_delivery_on_time = $date;
        $mwsPart->ecd_finish_workdays = $workdays;

        $mwsPart->save();
    }

    public static function updateStatus($mwsPart)
    {
        $steps = $mwsPart->steps;

        if ($steps->isEmpty()) {
            $mwsPart->status = 'pending';
            $mwsPart->current_step = 0;
        }

        $completed = $steps->where('status', 'completed')->count();
        $total = $steps->count();

        if ($completed == $total) {
            $mwsPart->status = 'completed';
            $mwsPart->current_step = $total;
            $mwsPart->finish_date = now();
        } elseif ($completed > 0) {
            $mwsPart->status = 'in_progress';
            $mwsPart->current_step = $completed + 1;
            $mwsPart->finish_date = null;
        } else {
            $mwsPart->status = 'pending';
            $mwsPart->current_step = 1;
        }

        $mwsPart->save();
    }

    public static function updateDuration($mwsPart)
    {
        $totalMinutes = 0;

        foreach ($mwsPart->steps as $step) {
            if ($step->hours) {
                $parts = explode(':', $step->hours);
                if (count($parts) === 2) {
                    $totalMinutes += ((int) $parts[0] * 60) + (int) $parts[1];
                }
            }
        }

        $h = floor($totalMinutes / 60);
        $m = $totalMinutes % 60;

        $mwsPart->man_hours = sprintf('%02d:%02d', $h, $m);
        $mwsPart->save();
    }
}