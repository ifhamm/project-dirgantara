<?php

namespace App\Services;

use App\Models\MwsPart;
use Illuminate\Support\Facades\DB;

class IwoNumberService
{
    public static function generate(): string
    {
        try {
            $now = now()->timezone('Asia/Jakarta');
            $prefix = $now->format('ym'); // YYMM

            $last = MwsPart::where('iwo_no', 'like', $prefix . '-%')
                ->lockForUpdate()
                ->orderBy('iwo_no', 'desc')
                ->first();

            $next = 1;

            if ($last && $last->iwo_no) {
                try {
                    $parts = explode('-', $last->iwo_no);
                    $lastSeq = (int) ($parts[1] ?? 0);
                    $next = $lastSeq + 1;
                } catch (\Exception $e) {
                    $next = 1;
                }
            }

            return $prefix . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        } catch (\Exception $e) {
            return 'ERR-' . now()->format('ymdHis');
        }
    }
}