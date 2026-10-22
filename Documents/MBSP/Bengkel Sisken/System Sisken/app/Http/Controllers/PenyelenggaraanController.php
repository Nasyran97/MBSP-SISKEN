<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyelenggaraanController extends Controller
{
    public function list()
    {
        $currentUser = $this->getCurrentUser();

        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $canApplyPenyelenggaraan = $isSuperAdmin
            || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', 48);

        $canAcceptPenyelenggaraan = $isSuperAdmin
            || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', 49);

        $canConfirmPenyelenggaraan = $isSuperAdmin
            || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', 81);

        $penyelenggaraanQuery = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE as pv')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'pv.VEHICLE_ID')
            ->leftJoin('USERS as driver', 'driver.ID', '=', 'pv.DRIVER_ID')
            ->leftJoin('USERS as jkjg', 'jkjg.ID', '=', 'pv.JKJG_ID')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as pic', 'pic.ID', '=', 'p.USER_ID')
            ->leftJoin('KATEGORI_PENYELENGGARAAN as kp', 'kp.ID', '=', 'pv.KATEGORI_PENYELENGGARAAN_ID')
            ->leftJoin('PENYELENGGARAAN_ASSIGNMENT as pa', function ($join) {
                $join->on('pa.PENYELENGGARAAN_ID', '=', 'pv.ID')
                    ->whereRaw("UPPER(pa.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('MEKANIK_GROUP as mg', 'mg.ID', '=', 'pa.GROUP_ID')
            ->leftJoin('USERS as mekanik', 'mekanik.ID', '=', 'pa.USER_ID')
            ->leftJoin('BENGKEL_PANEL as bp', 'bp.ID', '=', 'pv.BENGKEL_PANEL_ID')
            ->leftJoin('USERS as jcu', 'jcu.NOSTAFF', '=', 'pv.JC_BY')
            ->leftJoin('USERS as pju', 'pju.NOSTAFF', '=', 'pv.PJ_BY')
            ->select(
                DB::raw('jcu.NAMA as "JC_NAMA"'),
                DB::raw('pv.JC_AT as "JC_AT"'),
                DB::raw('pju.NAMA as "PJ_NAMA"'),
                DB::raw('pv.PJ_AT as "PJ_AT"'),
                DB::raw('pv.REPAIR_MODE as "REPAIR_MODE"'),
                DB::raw('pv.BENGKEL_PANEL_ID as "BENGKEL_PANEL_ID"'),
                DB::raw('bp.NAMA_PANEL as "BENGKEL_PANEL_NAMA"'),
                DB::raw('pv.JC_REMARK as "JC_REMARK"'),
                DB::raw('pv.NO_ARAHAN_KERJA as "NO_ARAHAN_KERJA"'),
                DB::raw('pv.REMARK_REJECT as "REMARK_REJECT"'),
                DB::raw('pv.REJECTED_STAGE as "REJECTED_STAGE"'),
                DB::raw('pv.ID as "ID"'),
                DB::raw('pv.NO_KERJA as "NO_KERJA"'),
                DB::raw('pv.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('pv.DRIVER_ID as "DRIVER_ID"'),
                DB::raw('pv.JKJG_ID as "JKJG_ID"'),
                DB::raw('pv.DRIVER_PHONE_NUM as "DRIVER_PHONE_NUM"'),
                DB::raw('pv.ODOMETER as "ODOMETER"'),
                DB::raw('pv.KATEGORI_PENYELENGGARAAN_ID as "KATEGORI_PENYELENGGARAAN_ID"'),
                DB::raw('kp.NAMA_KATEGORI as "KATEGORI_PENYELENGGARAAN_NAMA"'),
                DB::raw('pv.REPAIR_REMARK as "REPAIR_REMARK"'),
                DB::raw('pv.REMARK_DONE as "REMARK_DONE"'),
                DB::raw('pv.BREAKDOWN_RESULT as "BREAKDOWN_RESULT"'),
                DB::raw('pv.TOWING as "TOWING"'),
                DB::raw('pv.LOCATION_LATITUDE as "LOCATION_LATITUDE"'),
                DB::raw('pv.LOCATION_LONGITUDE as "LOCATION_LONGITUDE"'),
                DB::raw('pv.LOCATION_NAME as "LOCATION_NAME"'),
                DB::raw('pv.STATUS as "STATUS"'),
                DB::raw('pv.STATUS_DATA as "STATUS_DATA"'),
                DB::raw('pv.CREATED_BY as "CREATED_BY"'),
                DB::raw('pv.CREATED_AT as "CREATED_AT"'),
                DB::raw('pv.PENGESAHAN_AT as "PENGESAHAN_AT"'),
                DB::raw('pv.COMPLETED_AT as "COMPLETED_AT"'),

                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),

                DB::raw('driver.NAMA as "DRIVER_NAME"'),
                DB::raw('driver.NOSTAFF as "DRIVER_NOSTAFF"'),

                DB::raw('jkjg.NAMA as "JKJG_NAME"'),
                DB::raw('jkjg.NOSTAFF as "JKJG_NOSTAFF"'),

                DB::raw('pic.NAMA as "PIC"'),
                DB::raw('pic.NOSTAFF as "PIC_NOSTAFF"'),

                DB::raw('pa.ASSIGNMENT_TYPE as "ASSIGNMENT_TYPE"'),
                DB::raw('mg.ID as "ASSIGNED_GROUP_ID"'),
                DB::raw('mg.GROUP_NAME as "ASSIGNED_GROUP_NAME"'),
                DB::raw('pa.USER_ID as "ASSIGNED_USER_ID"'),
                DB::raw('mekanik.NAMA as "ASSIGNED_MEKANIK_NAME"'),
                DB::raw('mekanik.NOSTAFF as "ASSIGNED_MEKANIK_NOSTAFF"'),
                DB::raw('mekanik.JABATAN as "ASSIGNED_MEKANIK_JABATAN"')
            )
            ->whereRaw("UPPER(pv.STATUS_DATA) IN ('ACTIVE', 'DELETE')");

        $currentUserId = $currentUser->ID ?? $currentUser->id ?? null;

        // Groups the current user is an active member of, so mekanik can see the
        // work orders assigned to their own group (or to them individually).
        $mekanikGroupIds = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->select(DB::raw('GROUP_ID as "GROUP_ID"'))
            ->where('USER_ID', $currentUserId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->get()
            ->map(fn ($m) => $m->GROUP_ID ?? $m->group_id ?? null)
            ->filter()
            ->values();

        $leaderGroupIds = DB::connection('oracle')
            ->table('MEKANIK_GROUP_MEMBER')
            ->select(DB::raw('GROUP_ID as "GROUP_ID"'))
            ->where('USER_ID', $currentUserId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->whereRaw("UPPER(IS_LEADER) = 'Y'")
            ->get()
            ->map(fn ($m) => $m->GROUP_ID ?? $m->group_id ?? null)
            ->filter()
            ->values();

        // Job Controllers (and Pegawai Jurutera) also see the jobs of the categories they handle.
        $jcKategori = $this->jcKategoriFor($currentUser);
        $isPegawaiJurutera = $isSuperAdmin || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', self::ROLE_PEGAWAI_JURUTERA);
        $visibleFlowKategori = $isPegawaiJurutera ? self::FLOW_KATEGORI : $jcKategori;

        if (!$isSuperAdmin && !$canAcceptPenyelenggaraan && !$canConfirmPenyelenggaraan) {
            $penyelenggaraanQuery->where(function ($query) use ($currentUserId, $mekanikGroupIds, $visibleFlowKategori) {
                $query->where('pv.CREATED_BY', session('nostaff'));

                if (!empty($visibleFlowKategori)) {
                    $placeholders = implode(',', array_fill(0, count($visibleFlowKategori), '?'));
                    $query->orWhereRaw("UPPER(REPLACE(kp.NAMA_KATEGORI, '_', ' ')) IN ({$placeholders})", array_values($visibleFlowKategori));
                }

                if ($mekanikGroupIds->isNotEmpty()) {
                    $query->orWhereIn('pa.GROUP_ID', $mekanikGroupIds->toArray());
                }

                if ($currentUserId) {
                    $query->orWhere('pa.USER_ID', $currentUserId);
                }
            });
        }

        $penyelenggaraan = $penyelenggaraanQuery
            ->orderBy('pv.ID', 'DESC')
            ->get();

        $groupIds = $penyelenggaraan
            ->map(fn ($row) => $row->ASSIGNED_GROUP_ID ?? $row->assigned_group_id ?? null)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $groupMembersMap = collect();

        if (!empty($groupIds)) {
            $groupMembersMap = DB::connection('oracle')
                ->table('MEKANIK_GROUP_MEMBER as m')
                ->leftJoin('USERS as u', 'u.ID', '=', 'm.USER_ID')
                ->select(
                    DB::raw('m.GROUP_ID as "GROUP_ID"'),
                    DB::raw('m.IS_LEADER as "IS_LEADER"'),
                    DB::raw('u.NAMA as "NAMA"'),
                    DB::raw('u.NOSTAFF as "NOSTAFF"'),
                    DB::raw('u.JABATAN as "JABATAN"')
                )
                ->whereIn('m.GROUP_ID', $groupIds)
                ->whereRaw("UPPER(m.STATUS_DATA) = 'ACTIVE'")
                ->orderByDesc(DB::raw("UPPER(m.IS_LEADER)"))
                ->orderBy('u.NAMA')
                ->get()
                ->groupBy(fn ($row) => $row->GROUP_ID ?? $row->group_id ?? null);
        }

        $bengkelPanels = collect();

        if ($isSuperAdmin || !empty($jcKategori)) {
            $bengkelPanels = DB::connection('oracle')
                ->table('BENGKEL_PANEL')
                ->select(
                    DB::raw('ID as "ID"'),
                    DB::raw('NAMA_PANEL as "NAMA_PANEL"'),
                    DB::raw('KOD_PANEL as "KOD_PANEL"'),
                    DB::raw('SERVIS as "SERVIS"')
                )
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->orderBy('NAMA_PANEL')
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->ID ?? $p->id,
                    'nama' => $p->NAMA_PANEL ?? $p->nama_panel,
                    'kod' => $p->KOD_PANEL ?? $p->kod_panel,
                    'servis' => $p->SERVIS ?? $p->servis,
                ])
                ->values();
        }

        return view('pages.penyelenggaraan.penyelenggaraan-list', [
            'jcKategori' => $jcKategori,
            'canApprovePj' => $isSuperAdmin || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', self::ROLE_PJ),
            'canApproveJurutera' => $isPegawaiJurutera,
            'bengkelPanels' => $bengkelPanels,
            'flowKategori' => self::FLOW_KATEGORI,
            'title' => 'Senarai Penyelenggaraan',
            'penyelenggaraan' => $penyelenggaraan,
            'groupMembersMap' => $groupMembersMap,
            'currentUserId' => $currentUserId,
            'leaderGroupIds' => $leaderGroupIds->toArray(),
            'canApplyPenyelenggaraan' => $canApplyPenyelenggaraan,
            'canAcceptPenyelenggaraan' => $canAcceptPenyelenggaraan,
            'canConfirmPenyelenggaraan' => $canConfirmPenyelenggaraan,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }

    public function create()
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->canCreatePenyelenggaraan($currentUser)) {
            abort(403, 'Anda tidak mempunyai akses untuk membuat permohonan penyelenggaraan.');
        }

        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        [$vehicles, $drivers, $jkjgUsers, $kategoriList] = $this->getPenyelenggaraanFormOptions($currentUser, $isSuperAdmin);

        return view('pages.penyelenggaraan.penyelenggaraan-add', [
            'title' => 'Permohonan Penyelenggaraan',
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'jkjgUsers' => $jkjgUsers,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function edit($id)
    {
        $currentUser = $this->getCurrentUser();
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return redirect()
                ->route('penyelenggaraan-list')
                ->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $createdBy = $penyelenggaraan->CREATED_BY ?? $penyelenggaraan->created_by ?? null;

        if (!$isSuperAdmin && $createdBy !== session('nostaff')) {
            abort(403, 'Anda tidak mempunyai akses untuk mengemaskini permohonan ini.');
        }

        $status = strtoupper(trim($penyelenggaraan->STATUS ?? $penyelenggaraan->status ?? ''));

        if ($status !== 'PENDING') {
            return redirect()
                ->route('penyelenggaraan-list')
                ->with('error', 'Permohonan ini tidak lagi berstatus pending dan tidak boleh dikemaskini.');
        }

        [$vehicles, $drivers, $jkjgUsers, $kategoriList] = $this->getPenyelenggaraanFormOptions($currentUser, $isSuperAdmin);

        return view('pages.penyelenggaraan.penyelenggaraan-edit', [
            'title' => 'Kemaskini Permohonan Penyelenggaraan',
            'penyelenggaraan' => $penyelenggaraan,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'jkjgUsers' => $jkjgUsers,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function update(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return redirect()
                ->route('penyelenggaraan-list')
                ->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $createdBy = $penyelenggaraan->CREATED_BY ?? $penyelenggaraan->created_by ?? null;

        if (!$isSuperAdmin && $createdBy !== session('nostaff')) {
            abort(403, 'Anda tidak mempunyai akses untuk mengemaskini permohonan ini.');
        }

        $status = strtoupper(trim($penyelenggaraan->STATUS ?? $penyelenggaraan->status ?? ''));

        if ($status !== 'PENDING') {
            return redirect()
                ->route('penyelenggaraan-list')
                ->with('error', 'Permohonan ini tidak lagi berstatus pending dan tidak boleh dikemaskini.');
        }

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'driver_phone_num' => 'nullable|string|max:30',
            'jkjg_id' => 'required',
            'odometer' => 'required|numeric|min:0',
            'kategori_penyelenggaraan_id' => 'required|exists:oracle.KATEGORI_PENYELENGGARAAN,ID',
            'repair_remark' => 'required|string',
        ], [
            'vehicle_id.required' => 'Sila pilih no plat kenderaan.',
            'driver_id.required' => 'Sila pilih pemandu.',
            'odometer.required' => 'Sila masukkan odometer.',
            'odometer.numeric' => 'Odometer mesti dalam format nombor.',
            'jkjg_id.required' => 'Sila pilih JKJG.',
            'kategori_penyelenggaraan_id.required' => 'Sila pilih jenis permohonan.',
            'kategori_penyelenggaraan_id.exists' => 'Jenis permohonan tidak sah.',
            'repair_remark.required' => 'Sila masukkan keterangan kerosakan.',
        ]);

        $kategoriPenyelenggaraanId = $request->kategori_penyelenggaraan_id;
        $kategoriNama = $this->getKategoriPenyelenggaraanNama($kategoriPenyelenggaraanId);

        if ($kategoriNama === 'BREAKDOWN') {
            $request->validate([
                'location_latitude' => 'required|numeric',
                'location_longitude' => 'required|numeric',
                'location_name' => 'required|string|max:500',
            ], [
                'location_latitude.required' => 'Sila pilih lokasi breakdown pada peta.',
                'location_longitude.required' => 'Sila pilih lokasi breakdown pada peta.',
                'location_name.required' => 'Sila masukkan catatan lokasi breakdown.',
            ]);
        }

        $locationLatitude = $kategoriNama === 'BREAKDOWN'
            ? $request->location_latitude
            : null;

        $locationLongitude = $kategoriNama === 'BREAKDOWN'
            ? $request->location_longitude
            : null;

        $locationName = $kategoriNama === 'BREAKDOWN'
            ? $request->location_name
            : null;

        DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->update([
                'VEHICLE_ID' => $request->vehicle_id,
                'DRIVER_ID' => $request->driver_id,
                'DRIVER_PHONE_NUM' => $request->driver_phone_num,
                'ODOMETER' => $request->odometer,
                'JKJG_ID' => $request->jkjg_id,
                'KATEGORI_PENYELENGGARAAN_ID' => $kategoriPenyelenggaraanId,
                'REPAIR_REMARK' => $request->repair_remark,
                'LOCATION_LATITUDE' => $locationLatitude,
                'LOCATION_LONGITUDE' => $locationLongitude,
                'LOCATION_NAME' => $locationName,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->route('penyelenggaraan-list')
            ->with('success', 'Permohonan penyelenggaraan berjaya dikemaskini.');
    }

    public function expirePendingRequests()
    {
        $now = now('Asia/Kuala_Lumpur');
        $cutoff = $now->copy()->setTime(17, 0, 0);

        if ($now->lt($cutoff)) {
            return response()->json([
                'success' => false,
                'message' => 'Proses ini hanya boleh dijalankan selepas pukul 5:00 PM.',
            ]);
        }

        $expired = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->whereRaw("UPPER(STATUS) = 'PENDING'")
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => 'SYSTEM',
                'UPDATED_AT' => $now,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan penyelenggaraan yang masih pending selepas 5:00 PM berjaya dipadam.',
            'expired' => $expired,
        ]);
    }

    public function completePancitRequests()
    {
        $now = now('Asia/Kuala_Lumpur');

        $completed = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->whereRaw("UPPER(STATUS) = 'IN PROGRESS'")
            ->whereIn('KATEGORI_PENYELENGGARAAN_ID', function ($query) {
                $query->select('ID')
                    ->from('KATEGORI_PENYELENGGARAAN')
                    ->whereRaw("UPPER(NAMA_KATEGORI) = 'PANCIT'");
            })
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS' => 'COMPLETED',
                'UPDATED_BY' => 'SYSTEM',
                'UPDATED_AT' => $now,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Permohonan penyelenggaraan PANCIT dalam proses berjaya ditukar ke status selesai.',
            'completed' => $completed,
        ]);
    }

    public function delete($id)
    {
        $currentUser = $this->getCurrentUser();
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return back()->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $createdBy = $penyelenggaraan->CREATED_BY ?? $penyelenggaraan->created_by ?? null;

        if (!$isSuperAdmin && $createdBy !== session('nostaff')) {
            abort(403, 'Anda tidak mempunyai akses untuk memadam permohonan ini.');
        }

        $affected = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod aktif dijumpai untuk dipadam.');
        }

        return back()->with('success', 'Permohonan penyelenggaraan berjaya dipadam.');
    }

    public function accept($id)
    {
        $currentUser = $this->getCurrentUser();
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $canAccept = $isSuperAdmin
            || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', 49);

        if (!$canAccept) {
            abort(403, 'Anda tidak mempunyai akses untuk menerima permohonan ini.');
        }

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return back()->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $status = strtoupper(trim($penyelenggaraan->STATUS ?? $penyelenggaraan->status ?? ''));

        if ($status !== 'PENDING') {
            return back()->with('error', 'Permohonan ini bukan dalam status pending.');
        }

        $kategoriId = $penyelenggaraan->KATEGORI_PENYELENGGARAAN_ID ?? $penyelenggaraan->kategori_penyelenggaraan_id ?? null;
        $kategoriNama = $this->getKategoriPenyelenggaraanNama($kategoriId);

        // Every request is accepted by Pegawai PUSKEB (kaunter) first. Flow kategori then go to Pengesahan
        // for Job Controller assessment; the mechanic is assigned once the Pegawai Jurutera approves.
        $isFlow = $this->isFlowKategori($kategoriNama);

        $requiresPengesahan = $isFlow || in_array($kategoriNama, [
            'BREAKDOWN',
            'SERVIS',
            'PEMBAIKAN_RINGAN',
            'PEMBAIKAN_BERAT',
            'KIMPALAN',
        ]);

        $nextStatus = $requiresPengesahan ? 'PENGESAHAN' : 'IN PROGRESS';

        $affected = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS' => $nextStatus,
                'PENDING_AT' => now('Asia/Kuala_Lumpur'),
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod aktif dijumpai untuk dikemaskini.');
        }

        if (!$isFlow) {
            $this->assignMekanik($id, $kategoriId);
        }

        return back()->with('success', 'Permohonan penyelenggaraan berjaya diterima.');
    }

    public function confirmPengesahan($id)
    {
        $currentUser = $this->getCurrentUser();

        $isSuperAdmin = $this->isSuperAdminUser($currentUser);
        $canConfirmPenyelenggaraan = $isSuperAdmin
            || $this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', 81);

        if (!$canConfirmPenyelenggaraan) {
            abort(403, 'Hanya Penolong Jurutera boleh mengesahkan permohonan ini.');
        }

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return back()->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $status = strtoupper(trim($penyelenggaraan->STATUS ?? $penyelenggaraan->status ?? ''));

        if ($status !== 'PENGESAHAN') {
            return back()->with('error', 'Permohonan ini bukan dalam status menunggu pengesahan.');
        }

        $kategoriNama = $this->getKategoriPenyelenggaraanNama(
            $penyelenggaraan->KATEGORI_PENYELENGGARAAN_ID ?? $penyelenggaraan->kategori_penyelenggaraan_id ?? null
        );

        if ($this->isFlowKategori($kategoriNama)) {
            return back()->with('error', 'Sila gunakan butang lulus / tolak untuk kategori ini.');
        }

        $now = now('Asia/Kuala_Lumpur');

        $affected = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS' => 'IN PROGRESS',
                'PENGESAHAN_AT' => $now,
                'UPDATED_BY' => session('nostaff'),
                'UPDATED_AT' => $now,
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod aktif dijumpai untuk disahkan.');
        }

        return back()->with('success', 'Permohonan penyelenggaraan berjaya disahkan.');
    }

    public function markDone(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser->ID ?? $currentUser->id ?? null;
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$penyelenggaraan) {
            return back()->with('error', 'Rekod penyelenggaraan tidak dijumpai.');
        }

        $status = strtoupper(trim($penyelenggaraan->STATUS ?? $penyelenggaraan->status ?? ''));

        if ($status !== 'IN PROGRESS') {
            return back()->with('error', 'Permohonan ini bukan dalam status dalam proses.');
        }

        $assignment = DB::connection('oracle')
            ->table('PENYELENGGARAAN_ASSIGNMENT')
            ->where('PENYELENGGARAAN_ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->first();

        // Jobs with no internal mechanic (bengkel panel / no mechanic flow) are closed by the Job Controller.
        $kategoriNamaDone = $this->getKategoriPenyelenggaraanNama(
            $penyelenggaraan->KATEGORI_PENYELENGGARAAN_ID ?? $penyelenggaraan->kategori_penyelenggaraan_id ?? null
        );
        $repairMode = strtoupper(trim($penyelenggaraan->REPAIR_MODE ?? $penyelenggaraan->repair_mode ?? ''));
        $isJcOfJob = $this->isFlowKategori($kategoriNamaDone)
            && in_array($this->normKategori($kategoriNamaDone), $this->jcKategoriFor($currentUser), true)
            && ($repairMode === 'BENGKEL_PANEL' || !$assignment);

        if (!$isSuperAdmin && !$isJcOfJob && (!$assignment || !$this->isAssignmentLeader($assignment, $currentUserId))) {
            abort(403, 'Hanya ketua kumpulan atau mekanik yang ditugaskan boleh menandakan selesai.');
        }

        $kategoriNama = $this->getKategoriPenyelenggaraanNama(
            $penyelenggaraan->KATEGORI_PENYELENGGARAAN_ID ?? $penyelenggaraan->kategori_penyelenggaraan_id ?? null
        );

        $breakdownResult = null;
        $towing = null;
        $remarkDone = $request->remark_done;

        if ($kategoriNama === 'BREAKDOWN') {
            // Breakdown: repaired on the spot (remark), or sent to PUSKEB / bengkel panel (towing yes/no).
            $request->validate([
                'breakdown_result' => 'required|in:PEMBAIKAN_TERUS,PUSKEB,BENGKEL_PANEL',
                'remark_done' => 'required|string',
                'towing' => 'required_if:breakdown_result,PUSKEB,BENGKEL_PANEL|nullable|in:Y,N',
            ], [
                'breakdown_result.required' => 'Sila pilih keputusan breakdown.',
                'breakdown_result.in' => 'Keputusan breakdown tidak sah.',
                'remark_done.required' => 'Sila masukkan catatan.',
                'towing.required_if' => 'Sila pilih sama ada towing diperlukan atau tidak.',
                'towing.in' => 'Pilihan towing tidak sah.',
            ]);

            $breakdownResult = $request->breakdown_result;

            $towing = $breakdownResult === 'PEMBAIKAN_TERUS' ? null : $request->towing;
        } else {
            $request->validate([
                'remark_done' => 'required|string',
            ], [
                'remark_done.required' => 'Sila masukkan catatan kerja yang telah dilakukan.',
            ]);
        }

        $now = now('Asia/Kuala_Lumpur');
        $updatedBy = session('nostaff') ?? 'SYSTEM';

        $affected = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS' => 'COMPLETED',
                'REMARK_DONE' => $remarkDone,
                'BREAKDOWN_RESULT' => $breakdownResult,
                'TOWING' => $towing,
                'COMPLETED_AT' => $now,
                'UPDATED_BY' => $updatedBy,
                'UPDATED_AT' => $now,
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod aktif dijumpai untuk dikemaskini.');
        }

        if ($assignment) {
            DB::connection('oracle')
                ->table('PENYELENGGARAAN_ASSIGNMENT')
                ->where('ID', $assignment->ID ?? $assignment->id)
                ->update([
                    'STATUS' => 'COMPLETED',
                    'COMPLETED_AT' => $now,
                    'UPDATED_BY' => $updatedBy,
                    'UPDATED_AT' => $now,
                ]);
        }

        return back()->with('success', 'Permohonan penyelenggaraan berjaya ditandakan selesai.');
    }

    // ---- Job Controller -> Penolong Jurutera -> Pegawai Jurutera flow ----
    // Kimpalan, Aircond, Pembaikan Berat/Ringan and Servis. Breakdown and Pancit keep the old flow.

    private const ROLE_PJ = 81;
    private const ROLE_PEGAWAI_JURUTERA = 101;
    private const ROLE_JC_MEKANIK = 121;
    private const ROLE_JC_WELDING = 122;

    private const FLOW_KATEGORI = ['PEMBAIKAN BERAT', 'PEMBAIKAN RINGAN', 'SERVIS', 'KIMPALAN', 'AIRCOND'];
    private const JC_WELDING_KATEGORI = ['KIMPALAN'];
    private const JC_MEKANIK_KATEGORI = ['PEMBAIKAN BERAT', 'PEMBAIKAN RINGAN', 'SERVIS', 'AIRCOND'];

    private const STATUS_PENGESAHAN_JURUTERA = 'PENGESAHAN JURUTERA';

    private function normKategori($nama)
    {
        return strtoupper(trim(str_replace('_', ' ', (string) $nama)));
    }

    private function isFlowKategori($nama)
    {
        return in_array($this->normKategori($nama), self::FLOW_KATEGORI, true);
    }

    // Kategori names the user may assess as Job Controller (users with both roles get both sets).
    private function jcKategoriFor($user)
    {
        if ($this->isSuperAdminUser($user)) {
            return self::FLOW_KATEGORI;
        }

        $allowed = [];

        if ($this->hasUserModuleRole($user, 'PENYELENGGARAAN', self::ROLE_JC_WELDING)) {
            $allowed = array_merge($allowed, self::JC_WELDING_KATEGORI);
        }

        if ($this->hasUserModuleRole($user, 'PENYELENGGARAAN', self::ROLE_JC_MEKANIK)) {
            $allowed = array_merge($allowed, self::JC_MEKANIK_KATEGORI);
        }

        return array_values(array_unique($allowed));
    }

    // Loads the job and checks it is a flow kategori in the expected status; returns [row, kategoriNama] or a redirect.
    private function loadFlowJob($id, $expectedStatus)
    {
        $row = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->first();

        if (!$row) {
            return [null, null, back()->with('error', 'Rekod penyelenggaraan tidak dijumpai.')];
        }

        $kategoriNama = $this->getKategoriPenyelenggaraanNama(
            $row->KATEGORI_PENYELENGGARAAN_ID ?? $row->kategori_penyelenggaraan_id ?? null
        );

        if (!$this->isFlowKategori($kategoriNama)) {
            return [null, null, back()->with('error', 'Kategori ini tidak menggunakan aliran kelulusan Job Controller.')];
        }

        $status = strtoupper(trim($row->STATUS ?? $row->status ?? ''));

        if ($expectedStatus !== null && $status !== $expectedStatus) {
            return [null, null, back()->with('error', 'Status permohonan telah berubah. Sila muat semula halaman.')];
        }

        return [$row, $kategoriNama, null];
    }

    public function jcAssess(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();

        [$row, $kategoriNama, $redirect] = $this->loadFlowJob($id, null);

        if ($redirect) {
            return $redirect;
        }

        // Job Controller assesses after Pegawai PUSKEB has accepted (Pengesahan, not yet assessed).
        $rowStatus = strtoupper(trim($row->STATUS ?? $row->status ?? ''));
        $assessed = trim((string) ($row->REPAIR_MODE ?? $row->repair_mode ?? '')) !== '';

        if (!($rowStatus === 'PENGESAHAN' && !$assessed)) {
            return back()->with('error', 'Status permohonan telah berubah. Sila muat semula halaman.');
        }

        if (!in_array($this->normKategori($kategoriNama), $this->jcKategoriFor($currentUser), true)) {
            abort(403, 'Anda tidak mempunyai akses untuk menilai permohonan kategori ini.');
        }

        $request->validate([
            'repair_mode' => 'required|in:INTERNAL,BENGKEL_PANEL',
            'bengkel_panel_id' => 'required_if:repair_mode,BENGKEL_PANEL|nullable|exists:oracle.BENGKEL_PANEL,ID',
            'jc_remark' => 'required|string',
        ], [
            'repair_mode.required' => 'Sila pilih jenis pembaikan.',
            'repair_mode.in' => 'Jenis pembaikan tidak sah.',
            'bengkel_panel_id.required_if' => 'Sila pilih bengkel panel.',
            'bengkel_panel_id.exists' => 'Bengkel panel tidak sah.',
            'jc_remark.required' => 'Sila masukkan penilaian masalah.',
        ]);

        $now = now('Asia/Kuala_Lumpur');
        $by = session('nostaff') ?? 'SYSTEM';

        DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->update([
                'STATUS' => 'PENGESAHAN',
                'REPAIR_MODE' => $request->repair_mode,
                'BENGKEL_PANEL_ID' => $request->repair_mode === 'BENGKEL_PANEL' ? $request->bengkel_panel_id : null,
                'JC_REMARK' => $request->jc_remark,
                'JC_BY' => $by,
                'JC_AT' => $now,
                'PENDING_AT' => $now,
                'UPDATED_BY' => $by,
                'UPDATED_AT' => $now,
            ]);

        return back()->with('success', 'Penilaian dihantar kepada Penolong Jurutera untuk pengesahan.');
    }

    public function pjDecision(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->isSuperAdminUser($currentUser)
            && !$this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', self::ROLE_PJ)) {
            abort(403, 'Hanya Penolong Jurutera boleh membuat keputusan ini.');
        }

        [$row, , $redirect] = $this->loadFlowJob($id, 'PENGESAHAN');

        if ($redirect) {
            return $redirect;
        }

        if (trim((string) ($row->REPAIR_MODE ?? $row->repair_mode ?? '')) === '') {
            return back()->with('error', 'Permohonan ini belum dinilai oleh Job Controller.');
        }

        return $this->decide($request, $id, $row, 'PJ', self::STATUS_PENGESAHAN_JURUTERA);
    }

    public function masterDecision(Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->isSuperAdminUser($currentUser)
            && !$this->hasUserModuleRole($currentUser, 'PENYELENGGARAAN', self::ROLE_PEGAWAI_JURUTERA)) {
            abort(403, 'Hanya Pegawai Jurutera boleh meluluskan arahan kerja.');
        }

        [$row, , $redirect] = $this->loadFlowJob($id, self::STATUS_PENGESAHAN_JURUTERA);

        if ($redirect) {
            return $redirect;
        }

        return $this->decide($request, $id, $row, 'JURUTERA', 'IN PROGRESS');
    }

    // Shared approve / deny step. Approve by PJ also generates the arahan kerja number.
    private function decide(Request $request, $id, $row, $stage, $nextStatus)
    {
        $request->validate([
            'decision' => 'required|in:approve,deny',
            'remark_reject' => 'required_if:decision,deny|nullable|string',
        ], [
            'decision.required' => 'Sila pilih keputusan.',
            'remark_reject.required_if' => 'Sila masukkan sebab penolakan.',
        ]);

        $now = now('Asia/Kuala_Lumpur');
        $by = session('nostaff') ?? 'SYSTEM';

        if ($request->decision === 'deny') {
            DB::connection('oracle')
                ->table('PENYELENGGARAAN_VEHICLE')
                ->where('ID', $id)
                ->update([
                    'STATUS' => 'CANCELLED',
                    'REMARK_REJECT' => $request->remark_reject,
                    'REJECTED_BY' => $by,
                    'REJECTED_AT' => $now,
                    'REJECTED_STAGE' => $stage,
                    'UPDATED_BY' => $by,
                    'UPDATED_AT' => $now,
                ]);

            return back()->with('success', 'Permohonan ditolak dan dipindahkan ke tab Batal.');
        }

        $update = [
            'STATUS' => $nextStatus,
            'UPDATED_BY' => $by,
            'UPDATED_AT' => $now,
        ];

        if ($stage === 'PJ') {
            $update['PJ_BY'] = $by;
            $update['PJ_AT'] = $now;
            $update['PENGESAHAN_AT'] = $now;
        } else {
            // Arahan kerja is issued once, when the request moves from pending to dalam proses.
            $existing = $row->NO_ARAHAN_KERJA ?? $row->no_arahan_kerja ?? null;
            $update['NO_ARAHAN_KERJA'] = $existing ?: \App\Helpers\NoKerjaHelper::next();
            $update['JURUTERA_BY'] = $by;
            $update['JURUTERA_AT'] = $now;
        }

        DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->where('ID', $id)
            ->update($update);

        if ($stage === 'JURUTERA') {
            $this->assignMekanik(
                $id,
                $row->KATEGORI_PENYELENGGARAAN_ID ?? $row->kategori_penyelenggaraan_id ?? null
            );

            return back()->with('success', 'Arahan kerja diluluskan. Permohonan kini dalam proses.');
        }

        return back()->with('success', 'Permohonan diluluskan dan dihantar kepada Pegawai Jurutera.');
    }

    // Searchable alat ganti list for the Job Controller's info panel.
    public function alatGantiSearch(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (empty($this->jcKategoriFor($currentUser))) {
            abort(403, 'Anda tidak mempunyai akses.');
        }

        $q = strtoupper(trim((string) $request->query('q', '')));

        $query = DB::connection('oracle')
            ->table('ALAT_GANTI')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAME as "NAME"'),
                DB::raw('QUANTITY as "QUANTITY"')
            )
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'");

        if ($q !== '') {
            $query->whereRaw('UPPER(NAME) LIKE ?', ['%' . $q . '%']);
        }

        $items = $query
            ->orderBy('NAME')
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->ID ?? $r->id,
                'name' => $r->NAME ?? $r->name,
                'quantity' => (int) ($r->QUANTITY ?? $r->quantity ?? 0),
            ]);

        return response()->json($items);
    }

    private function isAssignmentLeader($assignment, $currentUserId)
    {
        if (!$currentUserId) {
            return false;
        }

        $assignmentType = strtoupper(trim($assignment->ASSIGNMENT_TYPE ?? $assignment->assignment_type ?? ''));

        if ($assignmentType === 'INDIVIDUAL') {
            $assignedUserId = $assignment->USER_ID ?? $assignment->user_id ?? null;

            return $assignedUserId && (string) $assignedUserId === (string) $currentUserId;
        }

        if ($assignmentType === 'GROUP') {
            $groupId = $assignment->GROUP_ID ?? $assignment->group_id ?? null;

            if (!$groupId) {
                return false;
            }

            return DB::connection('oracle')
                ->table('MEKANIK_GROUP_MEMBER')
                ->where('GROUP_ID', $groupId)
                ->where('USER_ID', $currentUserId)
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->whereRaw("UPPER(IS_LEADER) = 'Y'")
                ->exists();
        }

        return false;
    }

    public function store(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->canCreatePenyelenggaraan($currentUser)) {
            abort(403, 'Anda tidak mempunyai akses untuk membuat permohonan penyelenggaraan.');
        }

        $currentUserId = $currentUser->ID ?? $currentUser->id ?? null;
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'driver_phone_num' => 'nullable|string|max:30',
            'jkjg_id' => 'required',
            'odometer' => 'required|numeric|min:0',
            'kategori_penyelenggaraan_id' => 'required|exists:oracle.KATEGORI_PENYELENGGARAAN,ID',
            'repair_remark' => 'required|string',
        ], [
            'vehicle_id.required' => 'Sila pilih no plat kenderaan.',
            'driver_id.required' => 'Sila pilih pemandu.',
            'odometer.required' => 'Sila masukkan odometer.',
            'odometer.numeric' => 'Odometer mesti dalam format nombor.',
            'jkjg_id.required' => 'Sila pilih JKJG.',
            'kategori_penyelenggaraan_id.required' => 'Sila pilih jenis permohonan.',
            'kategori_penyelenggaraan_id.exists' => 'Jenis permohonan tidak sah.',
            'repair_remark.required' => 'Sila masukkan keterangan kerosakan.',
        ]);

        $kategoriPenyelenggaraanId = $request->kategori_penyelenggaraan_id;
        $kategoriNama = $this->getKategoriPenyelenggaraanNama($kategoriPenyelenggaraanId);

        if ($kategoriNama === 'BREAKDOWN') {
            $request->validate([
                'location_latitude' => 'required|numeric',
                'location_longitude' => 'required|numeric',
                'location_name' => 'required|string|max:500',
            ], [
                'location_latitude.required' => 'Sila pilih lokasi breakdown pada peta.',
                'location_longitude.required' => 'Sila pilih lokasi breakdown pada peta.',
                'location_name.required' => 'Sila masukkan catatan lokasi breakdown.',
            ]);
        }

        $locationLatitude = $kategoriNama === 'BREAKDOWN'
            ? $request->location_latitude
            : null;

        $locationLongitude = $kategoriNama === 'BREAKDOWN'
            ? $request->location_longitude
            : null;

        $locationName = $kategoriNama === 'BREAKDOWN'
            ? $request->location_name
            : null;

        DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->insert([
                'VEHICLE_ID' => $request->vehicle_id,
                'DRIVER_ID' => $request->driver_id,
                'DRIVER_PHONE_NUM' => $request->driver_phone_num,
                'ODOMETER' => $request->odometer,
                'JKJG_ID' => $request->jkjg_id,
                'KATEGORI_PENYELENGGARAAN_ID' => $kategoriPenyelenggaraanId,
                'REPAIR_REMARK' => $request->repair_remark,
                'LOCATION_LATITUDE' => $locationLatitude,
                'LOCATION_LONGITUDE' => $locationLongitude,
                'LOCATION_NAME' => $locationName,
                'STATUS' => 'PENDING',
                'STATUS_DATA' => 'ACTIVE',
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->route('penyelenggaraan-list')
            ->with('success', 'Permohonan penyelenggaraan berjaya dihantar.');
    }

    private function getPenyelenggaraanFormOptions($currentUser, $isSuperAdmin)
    {
        $currentUserId = $currentUser->ID ?? $currentUser->id ?? null;

        $vehicleQuery = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.LOKASI_PENEMPATAN as "LOKASI_PENEMPATAN"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) = 'ACTIVE'")
            ->whereNotNull('v.NO_PLAT');

        if (!$isSuperAdmin) {
            $vehicleQuery->join('PENGAWAS_VEHICLE as p', function ($join) use ($currentUserId) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->where('p.USER_ID', '=', $currentUserId)
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            });
        }

        $vehicles = $vehicleQuery
            ->orderBy('v.NO_PLAT')
            ->get();

        $drivers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->whereNotNull('u.MODULE_ROLE')
            ->whereRaw("
                (
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == 65)')
                    OR
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == \"65\")')
                )
            ")
            ->orderBy('u.NAMA')
            ->get();

        $jkjgUsers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->whereNotNull('u.MODULE_ROLE')
            ->whereRaw("
                (
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == 66)')
                    OR
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == \"66\")')
                )
            ")
            ->orderBy('u.NAMA')
            ->get();

        $kategoriList = DB::connection('oracle')
            ->table('KATEGORI_PENYELENGGARAAN')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAMA_KATEGORI as "NAMA_KATEGORI"')
            )
            ->orderBy('NAMA_KATEGORI')
            ->get();

        return [$vehicles, $drivers, $jkjgUsers, $kategoriList];
    }

    private function getKategoriPenyelenggaraanNama($kategoriId)
    {
        if (!$kategoriId) {
            return '';
        }

        $nama = DB::connection('oracle')
            ->table('KATEGORI_PENYELENGGARAAN')
            ->where('ID', $kategoriId)
            ->value('NAMA_KATEGORI');

        return strtoupper(trim($nama ?? ''));
    }

    private function getCurrentUser()
{
    $nostaff = session('nostaff');

    if (!$nostaff) {
        abort(403, 'Sila log masuk semula.');
    }

    $user = DB::connection('oracle')
        ->table('USERS')
        ->where('NOSTAFF', $nostaff)
        ->first();

    if (!$user) {
        abort(403, 'Akaun tidak dijumpai.');
    }

    return $user;
}

private function hasUserModuleRole($user, $moduleName, $roleId)
{
    $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

    if (!is_array($moduleRole)) {
        return false;
    }

    $moduleName = strtoupper(trim($moduleName));
    $roleId = (int) $roleId;

    foreach ($moduleRole as $item) {
        $module = strtoupper(trim($item['module'] ?? ''));
        $role = (int) ($item['role'] ?? 0);

        if ($module === $moduleName && $role === $roleId) {
            return true;
        }
    }

    return false;
}

private function canCreatePenyelenggaraan($user)
{
    return $this->hasUserModuleRole($user, 'SUPERADMIN', 1)
        || $this->hasUserModuleRole($user, 'PENYELENGGARAAN', 48);
}

private function isSuperAdminUser($user)
{
    return $this->hasUserModuleRole($user, 'SUPERADMIN', 1);
}

// SERVICE/BREAKDOWN/WELDING are worked on by a MEKANIK_GROUP; MAINTENANCE goes to one mekanik.
private const MEKANIK_GROUP_CATEGORIES = ['SERVICE', 'BREAKDOWN', 'WELDING'];
private const MEKANIK_INDIVIDUAL_CATEGORIES = ['MAINTENANCE'];

private function assignMekanik($penyelenggaraanId, $kategoriId)
{
    if (!$kategoriId) {
        return;
    }

    $kategori = DB::connection('oracle')
        ->table('KATEGORI_PENYELENGGARAAN')
        ->where('ID', $kategoriId)
        ->first();

    if (!$kategori) {
        return;
    }

    $mekanikCategory = strtoupper(trim($kategori->MEKANIK_CATEGORY ?? $kategori->mekanik_category ?? ''));
    $isMekanikFlow = strtoupper(trim($kategori->IS_MEKANIK_FLOW ?? $kategori->is_mekanik_flow ?? '')) === 'Y';

    if (!$mekanikCategory || !$isMekanikFlow) {
        return;
    }

    $alreadyAssigned = DB::connection('oracle')
        ->table('PENYELENGGARAAN_ASSIGNMENT')
        ->where('PENYELENGGARAAN_ID', $penyelenggaraanId)
        ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
        ->exists();

    if ($alreadyAssigned) {
        return;
    }

    if (in_array($mekanikCategory, self::MEKANIK_GROUP_CATEGORIES)) {
        $groupIds = DB::connection('oracle')
            ->table('MEKANIK_GROUP')
            ->select(DB::raw('ID as "ID"'))
            ->where('CATEGORY_CODE', $mekanikCategory)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->get()
            ->map(fn ($g) => $g->ID ?? $g->id ?? null)
            ->filter()
            ->values();

        $groupId = $this->pickLeastBusyGroup($groupIds);

        if ($groupId) {
            $this->createMekanikAssignment($penyelenggaraanId, $mekanikCategory, 'GROUP', $groupId, null);
        }

        return;
    }

    if (in_array($mekanikCategory, self::MEKANIK_INDIVIDUAL_CATEGORIES)) {
        $userIds = DB::connection('oracle')
            ->table('MEKANIK_INDIVIDUAL_POOL as pool')
            ->select(DB::raw('pool.USER_ID as "USER_ID"'))
            ->where('pool.CATEGORY_CODE', $mekanikCategory)
            ->whereRaw("UPPER(pool.STATUS_DATA) = 'ACTIVE'")
            ->whereRaw("UPPER(pool.AUTO_ASSIGN_ENABLED) = 'Y'")
            ->get()
            ->map(fn ($u) => $u->USER_ID ?? $u->user_id ?? null)
            ->filter()
            ->values();

        $userId = $this->pickLeastBusyMekanik($userIds);

        if ($userId) {
            $this->createMekanikAssignment($penyelenggaraanId, $mekanikCategory, 'INDIVIDUAL', null, $userId);
        }
    }
}

private function pickLeastBusyGroup($groupIds)
{
    $groupIds = collect($groupIds)->filter()->values();

    if ($groupIds->isEmpty()) {
        return null;
    }

    $workload = DB::connection('oracle')
        ->table('PENYELENGGARAAN_ASSIGNMENT')
        ->select(
            DB::raw('GROUP_ID as "GROUP_ID"'),
            DB::raw('COUNT(*) as "OPEN_COUNT"')
        )
        ->whereIn('GROUP_ID', $groupIds->toArray())
        ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
        ->whereRaw("UPPER(STATUS) NOT IN ('COMPLETED', 'CANCELLED')")
        ->groupBy('GROUP_ID')
        ->get()
        ->mapWithKeys(fn ($row) => [
            ($row->GROUP_ID ?? $row->group_id) => (int) ($row->OPEN_COUNT ?? $row->open_count ?? 0),
        ]);

    return $groupIds->sortBy(fn ($id) => $workload[$id] ?? 0)->first();
}

private function pickLeastBusyMekanik($userIds)
{
    $userIds = collect($userIds)->filter()->values();

    if ($userIds->isEmpty()) {
        return null;
    }

    $workload = DB::connection('oracle')
        ->table('PENYELENGGARAAN_ASSIGNMENT')
        ->select(
            DB::raw('USER_ID as "USER_ID"'),
            DB::raw('COUNT(*) as "OPEN_COUNT"')
        )
        ->whereIn('USER_ID', $userIds->toArray())
        ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
        ->whereRaw("UPPER(STATUS) NOT IN ('COMPLETED', 'CANCELLED')")
        ->groupBy('USER_ID')
        ->get()
        ->mapWithKeys(fn ($row) => [
            ($row->USER_ID ?? $row->user_id) => (int) ($row->OPEN_COUNT ?? $row->open_count ?? 0),
        ]);

    return $userIds->sortBy(fn ($id) => $workload[$id] ?? 0)->first();
}

private function createMekanikAssignment($penyelenggaraanId, $mekanikCategory, $assignmentType, $groupId, $userId)
{
    $now = now('Asia/Kuala_Lumpur');
    $assignedBy = session('nostaff') ?? 'SYSTEM';

    DB::connection('oracle')
        ->table('PENYELENGGARAAN_ASSIGNMENT')
        ->insert([
            'PENYELENGGARAAN_ID' => $penyelenggaraanId,
            'CATEGORY_CODE' => $mekanikCategory,
            'ASSIGNMENT_TYPE' => $assignmentType,
            'GROUP_ID' => $groupId,
            'USER_ID' => $userId,
            'STATUS' => 'ASSIGNED',
            'ASSIGNED_AT' => $now,
            'ASSIGNED_BY' => $assignedBy,
            'STATUS_DATA' => 'ACTIVE',
            'CREATED_BY' => $assignedBy,
            'CREATED_AT' => $now,
        ]);
}
}