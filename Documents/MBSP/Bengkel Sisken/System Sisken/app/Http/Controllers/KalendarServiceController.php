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
            ->leftJoin('SERVIS_HARIAN as sh', 'sh.KALENDAR_SERVICE_ID', '=', 'k.ID')
            ->where('k.TAHUN', $year)
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('k.TARIKH')
            ->orderByRaw("CASE k.SESI WHEN 'PAGI' THEN 1 ELSE 2 END")
            ->orderBy('k.ID')
            ->select(
                DB::raw('k.ID as "ID"'),
                DB::raw("TO_CHAR(k.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('sh.STATUS as "FORM_STATUS"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"')
            )
            ->get()
            ->map(function ($row) {
                $r = array_change_key_case((array) $row, CASE_LOWER);

                return [
                    'id' => (int) $r['id'],
                    'date' => $r['tarikh'],
                    'sesi' => $r['sesi'],
                    // BELUM = no servis form yet; otherwise the form status (DRAFT / SUBMITTED / COMPLETE).
                    'status' => strtoupper(trim($r['form_status'] ?? '')) ?: 'BELUM',
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
            'canManage' => $this->canManage(),
            'today' => now('Asia/Kuala_Lumpur')->toDateString(),
            'slots' => self::SLOTS,
        ]);
    }

    // Ketua servis: move a whole day's cars to an empty day, or swap two days.
    public function moveDay(Request $request)
    {
        if (!$this->canManage()) {
            abort(403, 'Hanya ketua kumpulan servis boleh menukar jadual kalendar.');
        }

        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
            'mode' => 'required|in:move,swap',
        ], [
            'from.required' => 'Tarikh asal tidak sah.',
            'to.required' => 'Sila pilih tarikh sasaran.',
            'to.date_format' => 'Tarikh sasaran tidak sah.',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');
        $mode = $request->input('mode');
        $year = (int) substr($from, 0, 4);

        if ($from === $to) {
            return back()->with('error', 'Tarikh sasaran mestilah berbeza.');
        }

        if ((int) substr($to, 0, 4) !== $year) {
            return back()->with('error', 'Tukar hari hanya dibenarkan dalam tahun yang sama.');
        }

        $today = now('Asia/Kuala_Lumpur')->toDateString();

        if ($to < $today || ($mode === 'swap' && $from < $today)) {
            return back()->with('error', 'Tarikh yang sudah berlalu tidak boleh dijadikan sasaran.');
        }

        $toSlots = self::SLOTS[Carbon::parse($to)->dayOfWeekIso] ?? null;

        if (!$toSlots) {
            return back()->with('error', 'Tarikh sasaran jatuh pada hujung minggu.');
        }

        if ($this->holidaysForYear($year)->contains('d', $to)) {
            return back()->with('error', 'Tarikh sasaran ialah hari cuti.');
        }

        $fromEntries = $this->entriesOnDate($from);
        $toEntries = $this->entriesOnDate($to);

        if ($fromEntries->isEmpty()) {
            return back()->with('error', 'Tiada kenderaan dijadualkan pada tarikh asal.');
        }

        if ($mode === 'move' && $toEntries->isNotEmpty()) {
            return back()->with('error', 'Tarikh sasaran sudah mempunyai jadual. Gunakan "Tukar Hari" untuk menukar kedua-dua hari.');
        }

        if ($mode === 'swap' && $toEntries->isEmpty()) {
            return back()->with('error', 'Tarikh sasaran tiada jadual. Gunakan "Pindah Hari" untuk memindahkan.');
        }

        // Cars whose servis already started stay where they are.
        foreach ($fromEntries->concat($toEntries) as $e) {
            if ($e->started) {
                return back()->with('error', 'Servis ' . $e->plat . ' sudah dimulakan; hanya hari yang semua kenderaannya belum mula boleh dipindah atau ditukar.');
            }
        }

        // Each session's cars must fit the other day's slots (e.g. Jumaat has no petang).
        $pairs = [[$fromEntries, $toSlots, $to]];

        if ($mode === 'swap') {
            $pairs[] = [$toEntries, self::SLOTS[Carbon::parse($from)->dayOfWeekIso] ?? [], $from];
        }

        foreach ($pairs as [$entries, $slots, $date]) {
            foreach (['PAGI', 'PETANG'] as $sesi) {
                if ($entries->where('sesi', $sesi)->count() > ($slots[$sesi] ?? 0)) {
                    return back()->with('error', 'Sesi ' . strtolower($sesi) . ' tidak muat pada ' . Carbon::parse($date)->format('d/m/Y') . '.');
                }
            }
        }

        $nostaff = session('nostaff') ?? 'SYSTEM';
        $now = now('Asia/Kuala_Lumpur');

        DB::connection('oracle')->transaction(function () use ($fromEntries, $toEntries, $from, $to, $mode, $nostaff, $now) {
            $set = function ($entries, $date) use ($nostaff, $now) {
                DB::connection('oracle')
                    ->table('KALENDAR_SERVICE')
                    ->whereIn('ID', $entries->pluck('id')->all())
                    ->update([
                        'TARIKH' => DB::raw("TO_DATE('" . $date . "', 'YYYY-MM-DD')"),
                        'UPDATED_BY' => $nostaff,
                        'UPDATED_AT' => $now,
                    ]);
            };

            $set($fromEntries, $to);

            if ($mode === 'swap') {
                $set($toEntries, $from);
            }
        });

        $fmt = fn ($d) => Carbon::parse($d)->format('d/m/Y');

        return redirect()->route('kalendar-service', ['year' => $year])->with(
            'success',
            $mode === 'swap'
                ? 'Hari ' . $fmt($from) . ' ditukar dengan ' . $fmt($to) . '.'
                : count($fromEntries) . ' kenderaan dipindahkan dari ' . $fmt($from) . ' ke ' . $fmt($to) . '.'
        );
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

        $group = $this->serviceGroup();

        if (!$group) {
            return back()->with('error', 'Kumpulan ' . self::GROUP_CODE . ' tidak dijumpai.');
        }

        // Cars whose servis already started ("touched") keep their date; only the rest are rescheduled.
        $touched = DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('SERVIS_HARIAN as sh', 'sh.KALENDAR_SERVICE_ID', '=', 'k.ID')
            ->where('k.TAHUN', $year)
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->select(
                DB::raw('k.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw("TO_CHAR(k.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('k.SESI as "SESI"')
            )
            ->get()
            ->map(fn ($r) => array_change_key_case((array) $r, CASE_LOWER));

        $touchedVehicles = $touched->pluck('vehicle_id')->map(fn ($id) => (int) $id)->flip();
        $used = [];

        foreach ($touched as $t) {
            $used[$t['tarikh']][$t['sesi']] = ($used[$t['tarikh']][$t['sesi']] ?? 0) + 1;
        }

        $hadSchedule = DB::connection('oracle')
            ->table('KALENDAR_SERVICE')
            ->where('TAHUN', $year)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->exists();

        $vehicleIds = DB::connection('oracle')
            ->table('VEHICLE')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('ID')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => isset($touchedVehicles[$id]))
            ->values()
            ->all();

        if (empty($vehicleIds)) {
            return back()->with('error', $touched->isEmpty()
                ? 'Tiada kenderaan aktif untuk dijadualkan.'
                : 'Semua kenderaan sudah dimulakan servisnya; tiada yang perlu dijadualkan semula.');
        }

        $skipDates = $this->holidaysForYear($year)->pluck('d')->flip();

        $nostaff = session('nostaff') ?? 'SYSTEM';
        $now = now('Asia/Kuala_Lumpur');
        $today = now('Asia/Kuala_Lumpur')->startOfDay();
        $rows = [];
        $next = 0;
        $total = count($vehicleIds);

        // Regenerating during the year only fills days from today onward.
        $start = ($hadSchedule && $today->year === $year) ? $today->copy() : Carbon::create($year, 1, 1);

        for ($day = $start; $day->year === $year && $next < $total; $day->addDay()) {
            $slots = self::SLOTS[$day->dayOfWeekIso] ?? null;
            $date = $day->toDateString();

            if (!$slots || isset($skipDates[$date])) {
                continue;
            }

            foreach ($slots as $sesi => $count) {
                $free = $count - ($used[$date][$sesi] ?? 0);

                for ($i = 0; $i < $free && $next < $total; $i++) {
                    $rows[] = [
                        'TARIKH' => DB::raw("TO_DATE('" . $date . "', 'YYYY-MM-DD')"),
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
            // Only entries with no servis form yet are replaced.
            DB::connection('oracle')
                ->table('KALENDAR_SERVICE')
                ->where('TAHUN', $year)
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->whereRaw('NOT EXISTS (SELECT 1 FROM SERVIS_HARIAN sh WHERE sh.KALENDAR_SERVICE_ID = KALENDAR_SERVICE.ID)')
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

        if ($touched->isNotEmpty()) {
            $msg .= ' ' . $touched->count() . ' kenderaan yang sudah dimulakan servis dikekalkan.';
        }

        if ($next < $total) {
            $msg .= ' ' . ($total - $next) . ' kenderaan tidak dapat dijadualkan (slot tidak mencukupi).';
        }

        return redirect()->route('kalendar-service', ['year' => $year])->with('success', $msg);
    }

    // Active entries on a date, flagged when their servis form has already been started.
    private function entriesOnDate(string $date)
    {
        return DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('VEHICLE as v', 'v.ID', '=', 'k.VEHICLE_ID')
            ->leftJoin('SERVIS_HARIAN as sh', 'sh.KALENDAR_SERVICE_ID', '=', 'k.ID')
            ->whereRaw("TRUNC(k.TARIKH) = TO_DATE(?, 'YYYY-MM-DD')", [$date])
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->select(
                DB::raw('k.ID as "ID"'),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('v.NO_PLAT as "PLAT"'),
                DB::raw('sh.ID as "FORM_ID"')
            )
            ->get()
            ->map(function ($row) {
                $r = array_change_key_case((array) $row, CASE_LOWER);

                return (object) [
                    'id' => (int) $r['id'],
                    'sesi' => $r['sesi'],
                    'plat' => $r['plat'],
                    'started' => !empty($r['form_id']),
                ];
            });
    }

    // Ketua of the service group (or superadmin) may rearrange the calendar.
    private function canManage(): bool
    {
        $nostaff = session('nostaff');

        if (!$nostaff) {
            return false;
        }

        $user = DB::connection('oracle')->table('USERS')->where('NOSTAFF', $nostaff)->first();

        if (!$user) {
            return false;
        }

        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        foreach (is_array($moduleRole) ? $moduleRole : [] as $item) {
            if (strtoupper(trim($item['module'] ?? '')) === 'SUPERADMIN' && (int) ($item['role'] ?? 0) === 1) {
                return true;
            }
        }

        $group = $this->serviceGroup();

        return $group && DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->where('GROUP_ID', $group['id'])
            ->where('USER_ID', $user->ID ?? $user->id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->whereRaw("UPPER(IS_LEADER) = 'Y'")
            ->exists();
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
