<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenyelenggaraanController extends Controller
{
    public function list()
    {
        $penyelenggaraan = DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE as pv')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'pv.VEHICLE_ID')
            ->leftJoin('USERS as driver', 'driver.ID', '=', 'pv.DRIVER_ID')
            ->leftJoin('USERS as jkjg', 'jkjg.ID', '=', 'pv.JKJG_ID')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as pic', 'pic.ID', '=', 'p.USER_ID')
            ->select(
                DB::raw('pv.ID as "ID"'),
                DB::raw('pv.NO_KERJA as "NO_KERJA"'),
                DB::raw('pv.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('pv.DRIVER_ID as "DRIVER_ID"'),
                DB::raw('pv.JKJG_ID as "JKJG_ID"'),
                DB::raw('pv.DRIVER_PHONE_NUM as "DRIVER_PHONE_NUM"'),
                DB::raw('pv.ODOMETER as "ODOMETER"'),
                DB::raw('pv.REPAIR_CATEGORY as "REPAIR_CATEGORY"'),
                DB::raw('pv.REPAIR_REMARK as "REPAIR_REMARK"'),
                DB::raw('pv.LOCATION_LATITUDE as "LOCATION_LATITUDE"'),
                DB::raw('pv.LOCATION_LONGITUDE as "LOCATION_LONGITUDE"'),
                DB::raw('pv.LOCATION_NAME as "LOCATION_NAME"'),
                DB::raw('pv.STATUS as "STATUS"'),
                DB::raw('pv.STATUS_DATA as "STATUS_DATA"'),
                DB::raw('pv.CREATED_AT as "CREATED_AT"'),

                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),

                DB::raw('driver.NAMA as "DRIVER_NAME"'),
                DB::raw('driver.NOSTAFF as "DRIVER_NOSTAFF"'),

                DB::raw('jkjg.NAMA as "JKJG_NAME"'),
                DB::raw('jkjg.NOSTAFF as "JKJG_NOSTAFF"'),

                DB::raw('pic.NAMA as "PIC"'),
                DB::raw('pic.NOSTAFF as "PIC_NOSTAFF"')
            )
            ->whereRaw("UPPER(pv.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('pv.ID', 'DESC')
            ->get();

        return view('pages.penyelenggaraan.penyelenggaraan-list', [
            'title' => 'Senarai Penyelenggaraan',
            'penyelenggaraan' => $penyelenggaraan,
        ]);
    }

    public function create()
    {
        $currentUser = $this->getCurrentUser();

        if (!$this->canCreatePenyelenggaraan($currentUser)) {
            abort(403, 'Anda tidak mempunyai akses untuk membuat permohonan penyelenggaraan.');
        }

        $currentUserId = $currentUser->ID ?? $currentUser->id ?? null;
        $isSuperAdmin = $this->isSuperAdminUser($currentUser);

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

        return view('pages.penyelenggaraan.penyelenggaraan-add', [
            'title' => 'Permohonan Penyelenggaraan',
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'jkjgUsers' => $jkjgUsers,
        ]);
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
            'repair_category' => 'required|string|in:PENYELENGGARAAN,SERVIS,BREAKDOWN,PANCIT',
            'repair_remark' => 'required|string',

        
            'location_latitude' => 'nullable|required_if:repair_category,BREAKDOWN|numeric',
            'location_longitude' => 'nullable|required_if:repair_category,BREAKDOWN|numeric',
            'location_name' => 'nullable|required_if:repair_category,BREAKDOWN|string|max:500',
        ], [
            'vehicle_id.required' => 'Sila pilih no plat kenderaan.',
            'driver_id.required' => 'Sila pilih pemandu.',
            'odometer.required' => 'Sila masukkan odometer.',
            'odometer.numeric' => 'Odometer mesti dalam format nombor.',
            'jkjg_id.required' => 'Sila pilih JKJG.',
            'repair_category.required' => 'Sila pilih jenis permohonan.',
            'repair_category.in' => 'Jenis permohonan tidak sah.',
            'repair_remark.required' => 'Sila masukkan keterangan kerosakan.',
        
            'location_latitude.required_if' => 'Sila pilih lokasi breakdown pada peta.',
            'location_longitude.required_if' => 'Sila pilih lokasi breakdown pada peta.',
            'location_name.required_if' => 'Sila masukkan catatan lokasi breakdown.',
        ]);
    
        $noKerja = 'PNY-' . date('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        $repairCategory = strtoupper($request->repair_category);

        $locationLatitude = $repairCategory === 'BREAKDOWN'
            ? $request->location_latitude
            : null;

        $locationLongitude = $repairCategory === 'BREAKDOWN'
            ? $request->location_longitude
            : null;

        $locationName = $repairCategory === 'BREAKDOWN'
            ? $request->location_name
            : null;
    
        DB::connection('oracle')
            ->table('PENYELENGGARAAN_VEHICLE')
            ->insert([
                'NO_KERJA' => $noKerja,
                'VEHICLE_ID' => $request->vehicle_id,
                'DRIVER_ID' => $request->driver_id,
                'DRIVER_PHONE_NUM' => $request->driver_phone_num,
                'ODOMETER' => $request->odometer,
                'JKJG_ID' => $request->jkjg_id,
                'REPAIR_CATEGORY' => $repairCategory,
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
        || $this->hasUserModuleRole($user, 'PENYELENGGARAAN', 61);
}

private function isSuperAdminUser($user)
{
    return $this->hasUserModuleRole($user, 'SUPERADMIN', 1);
}
}