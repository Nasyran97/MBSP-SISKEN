<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class NoKerjaHelper
{
    // No Kerja format: K00001/2026, resetting to 00001 each new year.
    // Shared by PENYELENGGARAAN_VEHICLE.NO_KERJA / NO_ARAHAN_KERJA and SERVIS_HARIAN.NO_ARAHAN_KERJA so the sequence never collides.
    public static function next(): string
    {
        $year = now('Asia/Kuala_Lumpur')->format('Y');
        $max = 0;

        $sources = [
            ['PENYELENGGARAAN_VEHICLE', 'NO_KERJA'],
            ['PENYELENGGARAAN_VEHICLE', 'NO_ARAHAN_KERJA'],
            ['SERVIS_HARIAN', 'NO_ARAHAN_KERJA'],
        ];

        foreach ($sources as [$table, $column]) {
            $values = DB::connection('oracle')
                ->table($table)
                ->whereRaw("UPPER({$column}) LIKE ?", ['K%/' . $year])
                ->pluck(strtolower($column));

            foreach ($values as $value) {
                if (preg_match('/^K(\d+)\/' . $year . '$/i', trim((string) $value), $m)) {
                    $max = max($max, (int) $m[1]);
                }
            }
        }

        return 'K' . str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT) . '/' . $year;
    }
}
