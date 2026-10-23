<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

// use Illuminate\Support\Facades\Auth;

class RoadTaxController extends Controller
{
    private function getCurrentUserContext()
    {
        $nostaff = session('nostaff');

        $user = $nostaff
            ? DB::connection('oracle')->table('USERS')->where('nostaff', $nostaff)->first()
            : null;

        $isSuperAdmin = false;
        $isInsuransAdmin = false;
        $userId = $user->ID ?? $user->id ?? null;

        if ($user) {
            $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

            if (is_array($moduleRole)) {
                foreach ($moduleRole as $item) {
                    $module = strtoupper(trim($item['module'] ?? ''));
                    $roleId = (int) ($item['role'] ?? 0);

                    if ($module === 'SUPERADMIN' && $roleId === 1) {
                        $isSuperAdmin = true;
                        break;
                    }

                    if ($module === 'INSURANS' && $roleId === 21) {
                        $isInsuransAdmin = true;
                    }
                }
            }
        }

        // Insurans admin has full cukai jalan access, same as superadmin.
        $hasFullAccess = $isSuperAdmin || $isInsuransAdmin;

        return [$hasFullAccess, $userId, $hasFullAccess];
    }

    private function getMyPengawasVehicleIds($userId)
    {
        if (!$userId) {
            return [];
        }

        return DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->where('USER_ID', $userId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->pluck('vehicle_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function cukaiCarList()
    {
        $cars = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->where('p.STATUS_DATA', '=', 'ACTIVE');
            })
            ->select(
                'v.*',
                'p.NAMA as PENGAWAS_KENDERAAN',
                'p.NOSTAFF as PENGAWAS_NOSTAFF'
            )
            ->orderBy('v.ID', 'DESC')
            ->get();

        return view('pages.roadtax.cukai-jalan-car-list', [
            'title' => 'Senarai Cukai Jalan',
            'cars' => $cars,
        ]);
    }

    public function cukaiJalanInfoList(Request $request)
    {
        [$isSuperAdmin, $currentUserId, $canViewAll] = $this->getCurrentUserContext();
        $myPengawasVehicleIds = $this->getMyPengawasVehicleIds($currentUserId);

        if (!$canViewAll && empty($myPengawasVehicleIds)) {
            return view('pages.roadtax.cukai-jalan-info-list', [
                'title' => 'Maklumat Cukai Jalan',
                'cars' => collect(),
                'roadtaxHistories' => collect(),
                'isSuperAdmin' => $isSuperAdmin,
                'myPengawasVehicleIds' => $myPengawasVehicleIds,
            ]);
        }

        $query = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->leftJoin('VEHICLE_ROADTAX as r', function ($join) {
                $join->on('r.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("
                        r.ID = (
                            SELECT r2.ID
                            FROM VEHICLE_ROADTAX r2
                            WHERE r2.VEHICLE_ID = v.ID
                            ORDER BY
                                CASE
                                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(r2.TARIKH_MULA) AND TRUNC(r2.TARIKH_TAMAT) THEN 1
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN 2
                                    ELSE 3
                                END,
                                CASE
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN r2.TARIKH_MULA
                                END ASC,
                                r2.TARIKH_TAMAT DESC,
                                r2.ID DESC
                            FETCH FIRST 1 ROW ONLY
                        )
                    ");
            })
            ->select(
                DB::raw('v.ID as "id"'),
                DB::raw('v.NO_PLAT as "no_plat"'),
                DB::raw('v.JENIS_KENDERAAN as "jenis_kenderaan"'),
                DB::raw('v.JABATAN as "jabatan"'),
                DB::raw('v.LOKASI_PENEMPATAN as "lokasi_penempatan"'),

                DB::raw('u.NAMA as "pengawas_kenderaan"'),
                DB::raw('u.NOSTAFF as "no_staff_pengawas"'),
                DB::raw('u.JABATAN as "jabatan_pengawas"'),

                DB::raw('r.ID as "roadtax_id"'),
                DB::raw('r.TARIKH_MULA as "tarikh_mula"'),
                DB::raw('r.TARIKH_TAMAT as "tarikh_tamat"'),
                DB::raw('r.JKPTOJPJ as "jkptojpj"'),
                DB::raw('r.JKPTOJAB as "jkptojab"'),
                DB::raw('r.ROADTAX_STATUS as "roadtax_status"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) = 'ACTIVE'");

        if (!$canViewAll) {
            $query->whereIn('v.ID', $myPengawasVehicleIds);
        }

        if ($request->filled('tamat_year')) {
            $query->whereRaw("TO_CHAR(r.TARIKH_TAMAT, 'YYYY') = ?", [
                $request->tamat_year
            ]);

            if ($request->filled('tamat_month')) {
                $query->whereRaw("TO_CHAR(r.TARIKH_TAMAT, 'MM') = ?", [
                    str_pad($request->tamat_month, 2, '0', STR_PAD_LEFT)
                ]);
            }
        }

        $cars = $query
            ->orderBy('v.NO_PLAT')
            ->get();

        $roadtaxHistoriesQuery = DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->select(
                DB::raw('ID as "id"'),
                DB::raw('VEHICLE_ID as "vehicle_id"'),
                DB::raw('TARIKH_MULA as "tarikh_mula"'),
                DB::raw('TARIKH_TAMAT as "tarikh_tamat"'),
                DB::raw('JKPTOJPJ as "jkptojpj"'),
                DB::raw('JKPTOJAB as "jkptojab"'),
                DB::raw('ROADTAX_STATUS as "roadtax_status"')
            );

        if (!$canViewAll) {
            $roadtaxHistoriesQuery->whereIn('VEHICLE_ID', $myPengawasVehicleIds);
        }

        $roadtaxHistories = $roadtaxHistoriesQuery
            ->orderBy('VEHICLE_ID')
            ->orderBy('TARIKH_TAMAT', 'DESC')
            ->get()
            ->groupBy('vehicle_id');

        return view('pages.roadtax.cukai-jalan-info-list', [
            'title' => 'Maklumat Cukai Jalan',
            'cars' => $cars,
            'roadtaxHistories' => $roadtaxHistories,
            'isSuperAdmin' => $isSuperAdmin,
            'myPengawasVehicleIds' => $myPengawasVehicleIds,
        ]);
    }

    public function cukaiJalanEdaranList(Request $request)
    {
        $query = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')

            // Latest / current roadtax
            ->leftJoin('VEHICLE_ROADTAX as r', function ($join) {
                $join->on('r.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("
                        r.ID = (
                            SELECT r2.ID
                            FROM VEHICLE_ROADTAX r2
                            WHERE r2.VEHICLE_ID = v.ID
                            AND (UPPER(r2.STATUS_DATA) = 'ACTIVE' OR r2.STATUS_DATA IS NULL)
                            ORDER BY
                                CASE
                                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(r2.TARIKH_MULA) AND TRUNC(r2.TARIKH_TAMAT) THEN 1
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN 2
                                    ELSE 3
                                END,
                                CASE
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN r2.TARIKH_MULA
                                END ASC,
                                r2.TARIKH_TAMAT DESC,
                                r2.ID DESC
                            FETCH FIRST 1 ROW ONLY
                        )
                    ");
            })

            // Latest / current insurance policy
            ->leftJoin('INSURANS_POLISI as ip', function ($join) {
                $join->on('ip.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("
                        ip.ID = (
                            SELECT ip2.ID
                            FROM INSURANS_POLISI ip2
                            WHERE ip2.VEHICLE_ID = v.ID
                            AND (UPPER(ip2.STATUS_DATA) = 'ACTIVE' OR ip2.STATUS_DATA IS NULL)
                            ORDER BY
                                CASE
                                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(ip2.TARIKH_MULA_INSURANS) AND TRUNC(ip2.TARIKH_TAMAT_INSURANS) THEN 1
                                    WHEN TRUNC(ip2.TARIKH_MULA_INSURANS) > TRUNC(SYSDATE) THEN 2
                                    ELSE 3
                                END,
                                CASE
                                    WHEN TRUNC(ip2.TARIKH_MULA_INSURANS) > TRUNC(SYSDATE) THEN ip2.TARIKH_MULA_INSURANS
                                END ASC,
                                ip2.TARIKH_TAMAT_INSURANS DESC,
                                ip2.ID DESC
                            FETCH FIRST 1 ROW ONLY
                        )
                    ");
            })

            ->select(
                DB::raw('v.ID as "id"'),
                DB::raw('v.NO_PLAT as "no_plat"'),
                DB::raw('v.JENIS_KENDERAAN as "jenis_kenderaan"'),
                DB::raw('v.JABATAN as "jabatan"'),
                DB::raw('v.LOKASI_PENEMPATAN as "lokasi_penempatan"'),

                DB::raw('u.NAMA as "pengawas_kenderaan"'),
                DB::raw('u.NOSTAFF as "no_staff_pengawas"'),
                DB::raw('u.JABATAN as "jabatan_pengawas"'),

                DB::raw('r.TARIKH_MULA as "tarikh_mula"'),
                DB::raw('r.TARIKH_TAMAT as "tarikh_tamat"'),
                DB::raw('r.ROADTAX_STATUS as "roadtax_status"'),

                DB::raw('ip.TARIKH_TAMAT_INSURANS as "tarikh_tamat_insurans"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) = 'ACTIVE'");

        if ($request->filled('jabatan')) {
            $query->where('v.JABATAN', $request->jabatan);
        }

        if ($request->filled('tarikh_tamat')) {
            $query->whereDate('r.TARIKH_TAMAT', '<', $request->tarikh_tamat);
        }

        $cars = $query
            ->orderBy('v.NO_PLAT')
            ->get();

        $departments = DB::connection('oracle')
            ->table('VEHICLE')
            ->select(DB::raw('JABATAN as "jabatan"'))
            ->whereNotNull('JABATAN')
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->distinct()
            ->orderBy('JABATAN')
            ->get();

        return view('pages.roadtax.cukai-jalan-edaran-list', [
            'title' => 'Edaran Cukai Jalan',
            'cars' => $cars,
            'departments' => $departments,
        ]);
    }

    public function updateRoadtaxStatus()
    {
        $today = now()->toDateString();
        $twoMonthsLater = now()->addMonths(2)->toDateString();

        // 1. Future roadtax, not started yet
        $akanAktif = DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->where('STATUS_DATA', 'ACTIVE')
            ->whereDate('TARIKH_MULA', '>', $today)
            ->update([
                'ROADTAX_STATUS' => 'AKAN AKTIF',
                'UPDATED_AT' => now(),
                'UPDATED_BY' => 'SYSTEM',
            ]);

        // 2. Already expired
        $tamatTempoh = DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->where('STATUS_DATA', 'ACTIVE')
            ->whereDate('TARIKH_TAMAT', '<', $today)
            ->update([
                'ROADTAX_STATUS' => 'TAMAT TEMPOH',
                'UPDATED_AT' => now(),
                'UPDATED_BY' => 'SYSTEM',
            ]);

        // 3. Current roadtax, expiring within 2 months
        $akanTamatTempoh = DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->where('STATUS_DATA', 'ACTIVE')
            ->whereDate('TARIKH_MULA', '<=', $today)
            ->whereDate('TARIKH_TAMAT', '>=', $today)
            ->whereDate('TARIKH_TAMAT', '<=', $twoMonthsLater)
            ->update([
                'ROADTAX_STATUS' => 'AKAN TAMAT TEMPOH',
                'UPDATED_AT' => now(),
                'UPDATED_BY' => 'SYSTEM',
            ]);

        // 4. Current roadtax, still valid more than 2 months
        $aktif = DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->where('STATUS_DATA', 'ACTIVE')
            ->whereDate('TARIKH_MULA', '<=', $today)
            ->whereDate('TARIKH_TAMAT', '>', $twoMonthsLater)
            ->update([
                'ROADTAX_STATUS' => 'AKTIF',
                'UPDATED_AT' => now(),
                'UPDATED_BY' => 'SYSTEM',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Roadtax status updated successfully.',
            'updated' => [
                'akan_aktif' => $akanAktif,
                'tamat_tempoh' => $tamatTempoh,
                'akan_tamat_tempoh' => $akanTamatTempoh,
                'aktif' => $aktif,
            ],
        ]);
    }

    private function getRoadtaxStatus($tarikhMula, $tarikhTamat)
    {
        if (empty($tarikhMula) || empty($tarikhTamat)) {
            return null;
        }

        $today = now()->startOfDay();
        $mula = Carbon::parse($tarikhMula)->startOfDay();
        $tamat = Carbon::parse($tarikhTamat)->startOfDay();

        if ($today->lt($mula)) {
            return 'AKAN AKTIF';
        }

        if ($today->gt($tamat)) {
            return 'TAMAT TEMPOH';
        }

        if ($tamat->lte($today->copy()->addMonths(2))) {
            return 'AKAN TAMAT TEMPOH';
        }

        return 'AKTIF';
    }

    public function storeRoadtax(Request $request)
    {
        [$isSuperAdmin, $currentUserId] = $this->getCurrentUserContext();

        $vehicleId = $request->vehicle_id;

        if (!$vehicleId && $request->filled('roadtax_id')) {
            $vehicleId = DB::connection('oracle')
                ->table('VEHICLE_ROADTAX')
                ->where('ID', $request->roadtax_id)
                ->value('VEHICLE_ID');
        }

        if (!$isSuperAdmin) {
            $myPengawasVehicleIds = $this->getMyPengawasVehicleIds($currentUserId);

            if (!in_array((string) $vehicleId, $myPengawasVehicleIds)) {
                abort(403, 'Anda tidak mempunyai akses untuk mengemaskini cukai jalan kenderaan ini.');
            }
        }

        if ($request->mode === 'jkp') {
            $request->validate([
                'roadtax_id' => 'required',
                'jkptojpj' => 'nullable|date',
                'jkptojab' => 'nullable|date',
                'tarikh_mula' => 'nullable|date',
            ]);

            $data = [
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ];

            // Superadmin / Insurans admin may correct the start date; the end date follows (12 months).
            if ($isSuperAdmin && $request->filled('tarikh_mula')) {
                $start = Carbon::parse($request->tarikh_mula)->startOfDay();
                $end = $start->copy()->addMonths(12)->subDay();

                $data['TARIKH_MULA'] = $start->format('Y-m-d');
                $data['TARIKH_TAMAT'] = $end->format('Y-m-d');
                $data['ROADTAX_STATUS'] = $this->getRoadtaxStatus($start->format('Y-m-d'), $end->format('Y-m-d'));
            }

            if ($request->filled('jkptojpj')) {
                $data['JKPTOJPJ'] = $request->jkptojpj;
            }

            if ($request->filled('jkptojab')) {
                $data['JKPTOJAB'] = $request->jkptojab;
            }

            DB::connection('oracle')
                ->table('VEHICLE_ROADTAX')
                ->where('ID', $request->roadtax_id)
                ->update($data);

            return back()->with('success', 'Maklumat JKP berjaya dikemaskini.');
        }

        /*
            Renew roadtax:
            - No more 6 months
            - Always 12 months
            - User/frontend only sends new_tarikh_mula
            - Backend calculates new_tarikh_tamat automatically
        */
        $request->validate([
            'vehicle_id' => 'required',
            'new_tarikh_mula' => 'required|date',
            'jkptojpj' => 'nullable|date',
            'jkptojab' => 'nullable|date',
        ]);

        $newStart = \Carbon\Carbon::parse($request->new_tarikh_mula)->startOfDay();
        $newEnd = $newStart->copy()->addMonths(12)->subDay();

        $roadtaxStatus = $this->getRoadtaxStatus(
            $newStart->format('Y-m-d'),
            $newEnd->format('Y-m-d')
        );

        DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->insert([
                'VEHICLE_ID' => $request->vehicle_id,
                'TARIKH_MULA' => $newStart->format('Y-m-d'),
                'TARIKH_TAMAT' => $newEnd->format('Y-m-d'),
                'JKPTOJPJ' => $request->jkptojpj,
                'JKPTOJAB' => $request->jkptojab,
                'STATUS_DATA' => 'ACTIVE',
                'ROADTAX_STATUS' => $roadtaxStatus,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return back()->with('success', 'Rekod pembaharuan cukai jalan berjaya ditambah.');
    }

    // Superadmin / Insurans admin: correct the dates of an existing roadtax record.
    public function updateRoadtax(Request $request, $id)
    {
        [$hasFullAccess] = $this->getCurrentUserContext();

        abort_unless($hasFullAccess, 403, 'Anda tidak mempunyai akses untuk mengemaskini rekod cukai jalan.');

        $request->validate([
            'tarikh_mula' => 'required|date',
            'tarikh_tamat' => 'required|date|after_or_equal:tarikh_mula',
            'jkptojpj' => 'nullable|date',
            'jkptojab' => 'nullable|date',
        ], [
            'tarikh_tamat.after_or_equal' => 'Tarikh tamat mesti sama atau selepas tarikh mula.',
        ]);

        $exists = DB::connection('oracle')->table('VEHICLE_ROADTAX')->where('ID', $id)->exists();

        if (!$exists) {
            return back()->with('error', 'Rekod cukai jalan tidak dijumpai.');
        }

        $start = Carbon::parse($request->tarikh_mula)->format('Y-m-d');
        $end = Carbon::parse($request->tarikh_tamat)->format('Y-m-d');

        DB::connection('oracle')
            ->table('VEHICLE_ROADTAX')
            ->where('ID', $id)
            ->update([
                'TARIKH_MULA' => $start,
                'TARIKH_TAMAT' => $end,
                'JKPTOJPJ' => $request->filled('jkptojpj') ? $request->jkptojpj : null,
                'JKPTOJAB' => $request->filled('jkptojab') ? $request->jkptojab : null,
                'ROADTAX_STATUS' => $this->getRoadtaxStatus($start, $end),
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return back()->with('success', 'Rekod cukai jalan berjaya dikemaskini.');
    }

    public function printEdaranPdf(Request $request)
    {
        $ids = array_filter(explode(',', $request->selected_vehicle_ids));

        if (empty($ids)) {
            return back()->withErrors(['vehicle_ids' => 'Sila pilih kenderaan.']);
        }

        $cars = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->leftJoin('VEHICLE_ROADTAX as r', function ($join) {
                $join->on('r.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("
                        r.ID = (
                            SELECT r2.ID
                            FROM VEHICLE_ROADTAX r2
                            WHERE r2.VEHICLE_ID = v.ID
                            AND (UPPER(r2.STATUS_DATA) = 'ACTIVE' OR r2.STATUS_DATA IS NULL)
                            ORDER BY
                                CASE
                                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(r2.TARIKH_MULA) AND TRUNC(r2.TARIKH_TAMAT) THEN 1
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN 2
                                    ELSE 3
                                END,
                                CASE
                                    WHEN TRUNC(r2.TARIKH_MULA) > TRUNC(SYSDATE) THEN r2.TARIKH_MULA
                                END ASC,
                                r2.TARIKH_TAMAT DESC,
                                r2.ID DESC
                            FETCH FIRST 1 ROW ONLY
                        )
                    ");
            })
            ->leftJoin('INSURANS_POLISI as ip', function ($join) {
                $join->on('ip.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("
                        ip.ID = (
                            SELECT ip2.ID
                            FROM INSURANS_POLISI ip2
                            WHERE ip2.VEHICLE_ID = v.ID
                            AND (UPPER(ip2.STATUS_DATA) = 'ACTIVE' OR ip2.STATUS_DATA IS NULL)
                            ORDER BY
                                CASE
                                    WHEN TRUNC(SYSDATE) BETWEEN TRUNC(ip2.TARIKH_MULA_INSURANS) AND TRUNC(ip2.TARIKH_TAMAT_INSURANS) THEN 1
                                    WHEN TRUNC(ip2.TARIKH_MULA_INSURANS) > TRUNC(SYSDATE) THEN 2
                                    ELSE 3
                                END,
                                CASE
                                    WHEN TRUNC(ip2.TARIKH_MULA_INSURANS) > TRUNC(SYSDATE) THEN ip2.TARIKH_MULA_INSURANS
                                END ASC,
                                ip2.TARIKH_TAMAT_INSURANS DESC,
                                ip2.ID DESC
                            FETCH FIRST 1 ROW ONLY
                        )
                    ");
            })
            ->select(
                DB::raw('v.NO_PLAT as "no_plat"'),
                DB::raw('v.JENIS_KENDERAAN as "jenis_kenderaan"'),
                DB::raw('v.LOKASI_PENEMPATAN as "lokasi_penempatan"'),

                DB::raw('u.NAMA as "pengawas_kenderaan"'),
                DB::raw('u.NOSTAFF as "no_staff_pengawas"'),
                DB::raw('u.JABATAN as "jabatan_pengawas"'),

                DB::raw('v.JABATAN as "jabatan"'),
                DB::raw('r.TARIKH_MULA as "tarikh_mula"'),
                DB::raw('r.TARIKH_TAMAT as "tarikh_tamat"'),
                DB::raw('ip.TARIKH_TAMAT_INSURANS as "tarikh_tamat_insurans"')
            )
            ->whereIn('v.ID', $ids)
            ->orderBy('v.NO_PLAT')
            ->get();

        $jabatan = $request->filter_jabatan ?: 'SEMUA JABATAN';
        $tempoh = null;

        if (!empty($request->filter_tarikh_tamat)) {
            $tempoh = Carbon::parse($request->filter_tarikh_tamat)
                ->format('d/m/Y');
        }

        $pdf = Pdf::loadView('pages.roadtax.edaran-roadtax', [
            'cars' => $cars,
            'jabatan' => $jabatan,
            'tempoh' => $tempoh,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('senarai-cukai-jalan.pdf');
    }
}