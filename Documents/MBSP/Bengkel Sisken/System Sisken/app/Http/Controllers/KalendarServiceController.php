<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KalendarServiceController extends Controller
{
    // Every scheduled service is assigned to this mekanik group.
    private const GROUP_CODE = 'SERVICE-A';

    // Weekday (1=Mon..5=Fri) => slots per session. Saturday/Sunday are never scheduled.
    private const SLOTS = [
        1 => ['PAGI' => 3, 'PETANG' => 2],
        2 => ['PAGI' => 3, 'PETANG' => 2],
        3 => ['PAGI' => 3, 'PETANG' => 2],
        4 => ['PAGI' => 3, 'PETANG' => 2],
        5 => ['PAGI' => 3, 'PETANG' => 0],
    ];

    public function index(Request $request)
    {
        $year = (int) $request->query('year', date('Y'));

        $holidays = $this->holidaysForYear($year)->map(fn ($h) => [
            'date' => $h->d,
            'name' => $h->n,
            'scope' => $h->s,
        ])->values();

        $entries = DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('VEHICLE as v', 'v.ID', '=', 'k.VEHICLE_ID')
            ->where('k.TAHUN', $year)
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('k.TARIKH')
            ->orderByRaw("CASE k.SESI WHEN 'PAGI' THEN 1 ELSE 2 END")
            ->orderBy('k.ID')
            ->select(
                DB::raw("TO_CHAR(k.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"')
            )
            ->get()
            ->map(function ($row) {
                $r = array_change_key_case((array) $row, CASE_LOWER);

                return [
                    'date' => $r['tarikh'],
                    'sesi' => $r['sesi'],
                    'plat' => $r['no_plat'],
                    'model' => trim(($r['buatan'] ?? '') . ' ' . ($r['model'] ?? '')),
                ];
            })
            ->values();

        return view('pages.kalendar-service.kalendar-service', [
            'title' => 'Kalendar Servis',
            'year' => $year,
            'holidays' => $holidays,
            'entries' => $entries,
            'checklist' => $this->checklist(),
            'groupName' => $this->serviceGroup()['name'] ?? self::GROUP_CODE,
            'canGenerate' => $this->canGenerate(),
        ]);
    }

    public function generate(Request $request)
    {
        if (!$this->canGenerate()) {
            abort(403, 'Anda tidak dibenarkan menjana kalendar servis.');
        }

        $year = (int) $request->input('year');

        if ($year < 2000 || $year > 2100) {
            return back()->with('error', 'Tahun tidak sah.');
        }

        $vehicleIds = DB::connection('oracle')
            ->table('VEHICLE')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('ID')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($vehicleIds)) {
            return back()->with('error', 'Tiada kenderaan aktif untuk dijadualkan.');
        }

        $group = $this->serviceGroup();

        if (!$group) {
            return back()->with('error', 'Kumpulan ' . self::GROUP_CODE . ' tidak dijumpai.');
        }

        $skipDates = $this->holidaysForYear($year)->pluck('d')->flip();

        $nostaff = session('nostaff') ?? 'SYSTEM';
        $now = now('Asia/Kuala_Lumpur');
        $rows = [];
        $next = 0;
        $total = count($vehicleIds);

        for ($day = Carbon::create($year, 1, 1); $day->year === $year && $next < $total; $day->addDay()) {
            $slots = self::SLOTS[$day->dayOfWeekIso] ?? null;

            if (!$slots || isset($skipDates[$day->toDateString()])) {
                continue;
            }

            foreach ($slots as $sesi => $count) {
                for ($i = 0; $i < $count && $next < $total; $i++) {
                    $rows[] = [
                        'TARIKH' => DB::raw("TO_DATE('" . $day->toDateString() . "', 'YYYY-MM-DD')"),
                        'TAHUN' => $year,
                        'SESI' => $sesi,
                        'VEHICLE_ID' => $vehicleIds[$next++],
                        'GROUP_ID' => $group['id'],
                        'STATUS_DATA' => 'ACTIVE',
                        'CREATED_BY' => $nostaff,
                        'CREATED_AT' => $now,
                    ];
                }
            }
        }

        DB::connection('oracle')->transaction(function () use ($year, $rows, $nostaff, $now) {
            DB::connection('oracle')
                ->table('KALENDAR_SERVICE')
                ->where('TAHUN', $year)
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->update([
                    'STATUS_DATA' => 'DELETE',
                    'UPDATED_BY' => $nostaff,
                    'UPDATED_AT' => $now,
                ]);

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::connection('oracle')->table('KALENDAR_SERVICE')->insert($chunk);
            }
        });

        $msg = count($rows) . ' kenderaan telah dijadualkan untuk tahun ' . $year . '.';

        if ($next < $total) {
            $msg .= ' ' . ($total - $next) . ' kenderaan tidak dapat dijadualkan (slot setahun tidak mencukupi).';
        }

        return redirect()->route('kalendar-service', ['year' => $year])->with('success', $msg);
    }

    private function serviceGroup(): ?array
    {
        $row = DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->where('GROUP_CODE', self::GROUP_CODE)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->first();

        if (!$row) {
            return null;
        }

        $r = array_change_key_case((array) $row, CASE_LOWER);

        return ['id' => (int) $r['id'], 'name' => $r['group_name'] ?? self::GROUP_CODE];
    }

    private function checklist()
    {
        return DB::connection('oracle')
            ->table('CHECKLIST_SERVIS')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('SUSUNAN')
            ->orderBy('ID')
            ->pluck('nama')
            ->values();
    }

    // Public holidays to skip: both NATIONAL and PENANG scopes.
    private function holidaysForYear(int $year)
    {
        return DB::connection('oracle')
            ->table('PUBLIC_HOLIDAY')
            ->where('HOLIDAY_YEAR', $year)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->select(
                DB::raw("TO_CHAR(HOLIDAY_DATE, 'YYYY-MM-DD') as d"),
                DB::raw('HOLIDAY_NAME as n'),
                DB::raw('HOLIDAY_SCOPE as s')
            )
            ->get()
            ->map(function ($row) {
                $r = array_change_key_case((array) $row, CASE_LOWER);

                return (object) ['d' => $r['d'], 'n' => $r['n'], 's' => $r['s']];
            });
    }

    // Only module PENYELENGGARAAN (id 21) with role 101 (Pegawai Jurutera).
    private function canGenerate(): bool
    {
        $nostaff = session('nostaff');

        if (!$nostaff) {
            return false;
        }

        $user = DB::connection('oracle')->table('USERS')->where('NOSTAFF', $nostaff)->first();
        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        if (!is_array($moduleRole)) {
            return false;
        }

        foreach ($moduleRole as $item) {
            if (strtoupper(trim($item['module'] ?? '')) === 'PENYELENGGARAAN' && (int) ($item['role'] ?? 0) === 101) {
                return true;
            }
        }

        return false;
    }
}
