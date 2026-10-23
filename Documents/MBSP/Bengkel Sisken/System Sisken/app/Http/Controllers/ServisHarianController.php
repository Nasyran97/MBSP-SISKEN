<?php

namespace App\Http\Controllers;

use App\Helpers\NoKerjaHelper;
use App\Helpers\ServisKelulusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServisHarianController extends Controller
{
    private const JKJG_ROLE = ServisKelulusan::JKJG_ROLE;
    // Penolong Jurutera (role 81) is the third approver.
    private const JURUTERA_ROLE = ServisKelulusan::ASSISTANT_ROLE;

    public function index(Request $request)
    {
        $realToday = now('Asia/Kuala_Lumpur')->toDateString();
        $tarikh = (string) $request->query('tarikh', '');

        // Optional ?tarikh=YYYY-MM-DD (from Kalendar Servis); defaults to today.
        $today = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tarikh) && strtotime($tarikh) !== false
            ? $tarikh
            : $realToday;
        $user = $this->currentUser();

        $entries = DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('VEHICLE as v', 'v.ID', '=', 'k.VEHICLE_ID')
            ->leftJoin('SERVIS_HARIAN as s', 's.KALENDAR_SERVICE_ID', '=', 'k.ID')
            ->whereRaw("TRUNC(k.TARIKH) = TO_DATE(?, 'YYYY-MM-DD')", [$today])
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->orderByRaw("CASE k.SESI WHEN 'PAGI' THEN 1 ELSE 2 END")
            ->orderBy('k.ID')
            ->select(
                DB::raw('k.ID as "ID"'),
                DB::raw('k.GROUP_ID as "GROUP_ID"'),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.LOKASI_PENEMPATAN as "DEPO"'),
                DB::raw('s.STATUS as "STATUS"'),
                DB::raw('s.NO_ARAHAN_KERJA as "NO_ARAHAN_KERJA"')
            )
            ->get()
            ->map(fn ($row) => (object) array_change_key_case((array) $row, CASE_LOWER));

        $drivers = $this->driversByVehicle($entries->pluck('vehicle_id')->all());

        $entries = $entries->map(function ($e) use ($drivers) {
            $e->driver = $drivers[$e->vehicle_id]['nama'] ?? null;

            return $e;
        });

        $groupIds = $entries->pluck('group_id')->filter()->unique()->all();
        $canSwap = $this->isLeaderOfAny($user, $groupIds);

        return view('pages.servis-harian.servis-harian-list', [
            'title' => 'Servis Harian',
            'today' => $today,
            'isToday' => $today === $realToday,
            'entries' => $entries,
            'canSwap' => $canSwap,
            'swapCandidates' => $canSwap ? $this->swapCandidates($realToday, (int) substr($today, 0, 4)) : [],
        ]);
    }

    // Approval inbox: each user only sees forms that need (or got) their own approval; Pegawai Jurutera sees all.
    public function kelulusan()
    {
        $data = ServisKelulusan::forUser($this->currentUser());

        return view('pages.servis-harian.servis-harian-kelulusan', [
            'title' => 'Kelulusan Servis',
            'pending' => $data['pending'],
            'done' => $data['done'],
        ]);
    }

    public function show($id)
    {
        $user = $this->currentUser();
        $entry = $this->findEntry($id);

        if (!$entry) {
            return redirect()->route('servis-harian')->with('error', 'Jadual servis tidak dijumpai.');
        }

        // Remember where the user came from so "Kembali" returns there (not always to today).
        $prev = url()->previous();

        if ($prev && str_starts_with($prev, url('/')) && !str_starts_with($prev, route('servis-harian-show', $id))) {
            session(["servis_back.$id" => $prev]);
        }

        $backUrl = session("servis_back.$id") ?: route('servis-harian', ['tarikh' => $entry->tarikh]);

        $isLeader = $this->isLeader($user, $entry->group_id);
        // No row is created on open; it is created on the first save, and the no arahan kerja only on submit.
        $form = $this->findForm($id);

        $driver = $this->driversByVehicle([$entry->vehicle_id])[$entry->vehicle_id] ?? null;

        $checklist = DB::connection('oracle')
            ->table('CHECKLIST_SERVIS')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->orderBy('SUSUNAN')
            ->orderBy('ID')
            ->select(DB::raw('ID as "ID"'), DB::raw('NAMA as "NAMA"'))
            ->get()
            ->map(fn ($r) => (object) array_change_key_case((array) $r, CASE_LOWER));

        $items = [];

        if ($form) {
            $items = DB::connection('oracle')
                ->table('SERVIS_HARIAN_ITEM')
                ->where('SERVIS_HARIAN_ID', $form->id)
                ->select(
                    DB::raw('CHECKLIST_SERVIS_ID as "CHECKLIST_SERVIS_ID"'),
                    DB::raw('KEPUTUSAN as "KEPUTUSAN"'),
                    DB::raw('CATATAN as "CATATAN"'),
                    DB::raw('TINDAKAN as "TINDAKAN"')
                )
                ->get()
                ->mapWithKeys(function ($r) {
                    $r = (object) array_change_key_case((array) $r, CASE_LOWER);

                    return [$r->checklist_servis_id => $r];
                })
                ->all();
        }

        $status = $form->status ?? null;
        $driverUserId = $form->driver_user_id ?? ($driver['user_id'] ?? null);
        $userId = $user->ID ?? $user->id;

        $approvers = [];

        if ($form) {
            $approvers = $this->approverNames([$form->jkjg_user_id, $form->driver_approved_by, $form->jurutera_user_id]);
        }

        return view('pages.servis-harian.servis-harian-form', [
            'title' => 'Servis Harian',
            'entry' => $entry,
            'backUrl' => $backUrl,
            'form' => $form,
            'driver' => $driver,
            'checklist' => $checklist,
            'items' => $items,
            'approvers' => $approvers,
            'canEdit' => $isLeader && (!$form || $status === 'DRAFT'),
            'isLeader' => $isLeader,
            'canApproveJkjg' => $status === 'SUBMITTED' && !$form->jkjg_approved_at
                && $this->hasRole($user, 'PENYELENGGARAAN', self::JKJG_ROLE),
            'canApproveDriver' => $status === 'SUBMITTED' && !$form->driver_approved_at
                && $driverUserId && (string) $driverUserId === (string) $userId,
            'canApproveJurutera' => $status === 'SUBMITTED' && !$form->jurutera_approved_at
                && $this->hasRole($user, 'PENYELENGGARAAN', self::JURUTERA_ROLE),
        ]);
    }

    public function save(Request $request, $id)
    {
        $user = $this->currentUser();
        $entry = $this->findEntry($id);

        if (!$entry || !$this->isLeader($user, $entry->group_id)) {
            abort(403, 'Hanya ketua kumpulan servis boleh mengisi borang ini.');
        }

        $form = $this->findForm($id) ?? $this->createForm($entry);

        if ($form->status !== 'DRAFT') {
            return back()->with('error', 'Borang telah dihantar dan tidak boleh diubah.');
        }

        $submit = $request->input('action') === 'submit';

        $request->validate([
            'odometer' => ($submit ? 'required' : 'nullable') . '|integer|min:0',
            'masa_masuk' => ($submit ? 'required' : 'nullable') . '|date_format:H:i',
            'masa_keluar' => 'nullable|date_format:H:i',
            'items' => 'nullable|array',
            'items.*.keputusan' => 'nullable|in:OK,NOT OK',
            'items.*.catatan' => 'nullable|string|max:500',
            'items.*.tindakan' => 'nullable|string|max:500',
        ], [
            'odometer.required' => 'Sila isi odometer.',
            'masa_masuk.required' => 'Sila isi masa masuk.',
        ]);

        $checklistIds = DB::connection('oracle')
            ->table('CHECKLIST_SERVIS')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $input = $request->input('items', []);

        $driver = $this->driversByVehicle([$entry->vehicle_id])[$entry->vehicle_id] ?? null;
        $nostaff = session('nostaff') ?? 'SYSTEM';
        $now = now('Asia/Kuala_Lumpur');
        $userId = $user->ID ?? $user->id;

        DB::connection('oracle')->transaction(function () use ($form, $entry, $request, $input, $checklistIds, $driver, $nostaff, $now, $userId, $submit) {
            $update = [
                'DRIVER_USER_ID' => $driver['user_id'] ?? null,
                'NO_TELEFON' => $driver['telefon'] ?? null,
                'DEPO' => $entry->depo,
                'ODOMETER' => $request->input('odometer') !== null && $request->input('odometer') !== '' ? (int) $request->input('odometer') : null,
                'MASA_MASUK' => $request->input('masa_masuk') ?: null,
                'MASA_KELUAR' => $request->input('masa_keluar') ?: null,
                'UPDATED_BY' => $nostaff,
                'UPDATED_AT' => $now,
            ];

            if ($submit) {
                $update['NO_ARAHAN_KERJA'] = $form->no_arahan_kerja ?: NoKerjaHelper::next();
                $update['STATUS'] = 'SUBMITTED';
                $update['SUBMITTED_BY'] = $userId;
                $update['SUBMITTED_AT'] = $now;
            }

            DB::connection('oracle')->table('SERVIS_HARIAN')->where('ID', $form->id)->update($update);

            DB::connection('oracle')->table('SERVIS_HARIAN_ITEM')->where('SERVIS_HARIAN_ID', $form->id)->delete();

            $rows = [];

            foreach ($checklistIds as $cid) {
                $row = $input[$cid] ?? [];

                $rows[] = [
                    'SERVIS_HARIAN_ID' => $form->id,
                    'CHECKLIST_SERVIS_ID' => $cid,
                    'KEPUTUSAN' => $row['keputusan'] ?? null,
                    'CATATAN' => $row['catatan'] ?? null,
                    'TINDAKAN' => $row['tindakan'] ?? null,
                    'CREATED_AT' => $now,
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::connection('oracle')->table('SERVIS_HARIAN_ITEM')->insert($chunk);
            }
        });

        return redirect()->route('servis-harian-show', $id)->with(
            'success',
            $submit ? 'Borang servis dihantar untuk kelulusan.' : 'Borang servis disimpan.'
        );
    }

    public function approve($id, $type)
    {
        $user = $this->currentUser();
        $form = $this->findForm($id);

        if (!$form || $form->status !== 'SUBMITTED') {
            return back()->with('error', 'Borang belum dihantar untuk kelulusan.');
        }

        $userId = $user->ID ?? $user->id;
        $now = now('Asia/Kuala_Lumpur');

        switch ($type) {
            case 'jkjg':
                $allowed = $this->hasRole($user, 'PENYELENGGARAAN', self::JKJG_ROLE) && !$form->jkjg_approved_at;
                $update = ['JKJG_USER_ID' => $userId, 'JKJG_APPROVED_AT' => $now];
                break;
            case 'driver':
                $allowed = $form->driver_user_id && (string) $form->driver_user_id === (string) $userId && !$form->driver_approved_at;
                $update = ['DRIVER_APPROVED_BY' => $userId, 'DRIVER_APPROVED_AT' => $now];
                break;
            case 'jurutera':
                $allowed = $this->hasRole($user, 'PENYELENGGARAAN', self::JURUTERA_ROLE) && !$form->jurutera_approved_at;
                $update = ['JURUTERA_USER_ID' => $userId, 'JURUTERA_APPROVED_AT' => $now];
                break;
            default:
                abort(404);
        }

        if (!$allowed) {
            return back()->with('error', 'Anda tidak dibenarkan meluluskan atau telah meluluskan borang ini.');
        }

        $update['UPDATED_BY'] = session('nostaff') ?? 'SYSTEM';
        $update['UPDATED_AT'] = $now;

        DB::connection('oracle')->table('SERVIS_HARIAN')->where('ID', $form->id)->update($update);

        // Fully approved once every required approval is recorded (no driver => driver step not required).
        $fresh = $this->findForm($id);

        if ($fresh->jkjg_approved_at && $fresh->jurutera_approved_at && ($fresh->driver_approved_at || !$fresh->driver_user_id)) {
            DB::connection('oracle')->table('SERVIS_HARIAN')->where('ID', $form->id)->update(['STATUS' => 'COMPLETE']);
        }

        return redirect()->route('servis-harian-kelulusan')->with('success', 'Kelulusan berjaya direkodkan.');
    }

    // Ketua servis swaps this car's date/session with another scheduled car.
    public function swap(Request $request, $id)
    {
        $user = $this->currentUser();
        $request->validate(['target_id' => 'required|integer'], ['target_id.required' => 'Sila pilih kenderaan untuk ditukar.']);

        $a = $this->findEntry($id);
        $b = $this->findEntry($request->input('target_id'));

        if (!$a || !$b || $a->id === $b->id) {
            return back()->with('error', 'Jadual servis tidak sah.');
        }

        if (!$this->isLeader($user, $a->group_id)) {
            abort(403, 'Hanya ketua kumpulan servis boleh menukar tarikh servis.');
        }

        if ((int) $a->tahun !== (int) $b->tahun) {
            return back()->with('error', 'Tukar tarikh hanya dibenarkan dalam tahun yang sama.');
        }

        foreach ([$a, $b] as $e) {
            if ($this->findForm($e->id)) {
                return back()->with('error', 'Servis ' . $e->plat . ' sudah dimulakan; hanya kenderaan berstatus Belum Mula boleh ditukar tarikh.');
            }
        }

        $nostaff = session('nostaff') ?? 'SYSTEM';
        $now = now('Asia/Kuala_Lumpur');

        DB::connection('oracle')->transaction(function () use ($a, $b, $nostaff, $now) {
            foreach ([[$a, $b], [$b, $a]] as [$from, $to]) {
                DB::connection('oracle')->table('KALENDAR_SERVICE')->where('ID', $from->id)->update([
                    'TARIKH' => DB::raw("TO_DATE('" . $to->tarikh . "', 'YYYY-MM-DD')"),
                    'SESI' => $to->sesi,
                    'UPDATED_BY' => $nostaff,
                    'UPDATED_AT' => $now,
                ]);
            }
        });

        return redirect()->route('servis-harian')->with(
            'success',
            $a->plat . ' ditukar dengan ' . $b->plat . ' (' . $b->tarikh . ' ' . $b->sesi . ').'
        );
    }

    // ---------------------------------------------------------------- helpers

    private function findEntry($id)
    {
        $row = DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('VEHICLE as v', 'v.ID', '=', 'k.VEHICLE_ID')
            ->where('k.ID', $id)
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->select(
                DB::raw('k.ID as "ID"'),
                DB::raw('k.GROUP_ID as "GROUP_ID"'),
                DB::raw('k.TAHUN as "TAHUN"'),
                DB::raw("TO_CHAR(k.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.LOKASI_PENEMPATAN as "DEPO"')
            )
            ->first();

        return $row ? (object) array_change_key_case((array) $row, CASE_LOWER) : null;
    }

    private function findForm($kalendarId)
    {
        $row = DB::connection('oracle')
            ->table('SERVIS_HARIAN')
            ->where('KALENDAR_SERVICE_ID', $kalendarId)
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('DRIVER_USER_ID as "DRIVER_USER_ID"'),
                DB::raw('NO_TELEFON as "NO_TELEFON"'),
                DB::raw('DEPO as "DEPO"'),
                DB::raw('ODOMETER as "ODOMETER"'),
                DB::raw('MASA_MASUK as "MASA_MASUK"'),
                DB::raw('MASA_KELUAR as "MASA_KELUAR"'),
                DB::raw('NO_ARAHAN_KERJA as "NO_ARAHAN_KERJA"'),
                DB::raw('STATUS as "STATUS"'),
                DB::raw('JKJG_USER_ID as "JKJG_USER_ID"'),
                DB::raw('JKJG_APPROVED_AT as "JKJG_APPROVED_AT"'),
                DB::raw('DRIVER_APPROVED_BY as "DRIVER_APPROVED_BY"'),
                DB::raw('DRIVER_APPROVED_AT as "DRIVER_APPROVED_AT"'),
                DB::raw('JURUTERA_USER_ID as "JURUTERA_USER_ID"'),
                DB::raw('JURUTERA_APPROVED_AT as "JURUTERA_APPROVED_AT"')
            )
            ->first();

        return $row ? (object) array_change_key_case((array) $row, CASE_LOWER) : null;
    }

    private function createForm($entry)
    {
        $driver = $this->driversByVehicle([$entry->vehicle_id])[$entry->vehicle_id] ?? null;

        DB::connection('oracle')->transaction(function () use ($entry, $driver) {
            DB::connection('oracle')->table('SERVIS_HARIAN')->insert([
                'KALENDAR_SERVICE_ID' => $entry->id,
                'VEHICLE_ID' => $entry->vehicle_id,
                'TARIKH' => DB::raw("TO_DATE('" . $entry->tarikh . "', 'YYYY-MM-DD')"),
                'DRIVER_USER_ID' => $driver['user_id'] ?? null,
                'NO_TELEFON' => $driver['telefon'] ?? null,
                'DEPO' => $entry->depo,
                'STATUS' => 'DRAFT',
                'STATUS_DATA' => 'ACTIVE',
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
        });

        return $this->findForm($entry->id);
    }

    // vehicle_id => ['user_id', 'nama', 'telefon']
    private function driversByVehicle(array $vehicleIds): array
    {
        $vehicleIds = array_values(array_filter(array_unique($vehicleIds)));

        if (!$vehicleIds) {
            return [];
        }

        $out = [];

        $rows = DB::connection('oracle')
            ->table('DRIVER as d')
            ->join('USERS as u', 'u.ID', '=', 'd.USER_ID')
            ->whereIn('d.VEHICLE_ID', $vehicleIds)
            ->whereRaw("UPPER(d.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('d.ID')
            ->select(
                DB::raw('d.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('u.ID as "USER_ID"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.NO_TELEFON as "TELEFON"')
            )
            ->get();

        foreach ($rows as $row) {
            $r = array_change_key_case((array) $row, CASE_LOWER);
            $out[$r['vehicle_id']] ??= ['user_id' => $r['user_id'], 'nama' => $r['nama'], 'telefon' => $r['telefon']];
        }

        return $out;
    }

    private function approverNames(array $userIds): array
    {
        $userIds = array_values(array_filter(array_unique($userIds)));

        if (!$userIds) {
            return [];
        }

        return DB::connection('oracle')
            ->table('USERS')
            ->whereIn('ID', $userIds)
            ->select(DB::raw('ID as "ID"'), DB::raw('NAMA as "NAMA"'))
            ->get()
            ->mapWithKeys(function ($r) {
                $r = array_change_key_case((array) $r, CASE_LOWER);

                return [$r['id'] => $r['nama']];
            })
            ->all();
    }

    // Upcoming scheduled cars (same year) that can be swapped with.
    private function swapCandidates(string $from, int $year): array
    {
        return DB::connection('oracle')
            ->table('KALENDAR_SERVICE as k')
            ->join('VEHICLE as v', 'v.ID', '=', 'k.VEHICLE_ID')
            ->leftJoin('SERVIS_HARIAN as s', 's.KALENDAR_SERVICE_ID', '=', 'k.ID')
            ->whereRaw("TRUNC(k.TARIKH) >= TO_DATE(?, 'YYYY-MM-DD')", [$from])
            ->where('k.TAHUN', $year)
            ->whereRaw("UPPER(k.STATUS_DATA) = 'ACTIVE'")
            ->whereNull('s.ID') // only "Belum Mula": no servis form started yet
            ->orderBy('k.TARIKH')
            ->orderByRaw("CASE k.SESI WHEN 'PAGI' THEN 1 ELSE 2 END")
            ->orderBy('k.ID')
            ->select(
                DB::raw('k.ID as "ID"'),
                DB::raw("TO_CHAR(k.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('k.SESI as "SESI"'),
                DB::raw('v.NO_PLAT as "PLAT"')
            )
            ->get()
            ->map(fn ($r) => array_change_key_case((array) $r, CASE_LOWER))
            ->all();
    }

    private function currentUser()
    {
        $nostaff = session('nostaff');

        if (!$nostaff) {
            abort(403, 'Sila log masuk semula.');
        }

        $user = DB::connection('oracle')->table('USERS')->where('NOSTAFF', $nostaff)->first();

        if (!$user) {
            abort(403, 'Akaun tidak dijumpai.');
        }

        return $user;
    }

    private function hasRole($user, string $module, int $role): bool
    {
        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        if (!is_array($moduleRole)) {
            return false;
        }

        foreach ($moduleRole as $item) {
            if (strtoupper(trim($item['module'] ?? '')) === $module && (int) ($item['role'] ?? 0) === $role) {
                return true;
            }
        }

        return false;
    }

    private function isLeader($user, $groupId): bool
    {
        if ($this->hasRole($user, 'SUPERADMIN', 1)) {
            return true;
        }

        return $groupId && $this->isLeaderOfAny($user, [$groupId]);
    }

    private function isLeaderOfAny($user, array $groupIds): bool
    {
        if ($this->hasRole($user, 'SUPERADMIN', 1)) {
            return true;
        }

        $groupIds = array_values(array_filter($groupIds));

        if (!$groupIds) {
            return false;
        }

        return DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->whereIn('GROUP_ID', $groupIds)
            ->where('USER_ID', $user->ID ?? $user->id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->whereRaw("UPPER(IS_LEADER) = 'Y'")
            ->exists();
    }
}
