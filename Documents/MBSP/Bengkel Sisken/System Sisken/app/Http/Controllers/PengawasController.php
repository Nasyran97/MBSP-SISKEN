<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PengawasController extends Controller
{
    public function list()
    {
        $pengawas = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE as p')
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'p.VEHICLE_ID')
            ->select(
                DB::raw('p.ID as "ID"'),
                DB::raw('p.USER_ID as "USER_ID"'),
                DB::raw('p.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.KOD_JABATAN as "KOD_JABATAN"'),

                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('p.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('u.NAMA')
            ->orderBy('v.NO_PLAT')
            ->get()
            ->groupBy(function ($row) {
                return $row->USER_ID
                    ?? $row->user_id
                    ?? null;
            })
            ->map(function ($rows) {
                $first = $rows->first();

                $vehicles = $rows->map(function ($row) {
                    return (object) [
                        'ID' => $row->ID ?? $row->id ?? null,
                        'VEHICLE_ID' => $row->VEHICLE_ID ?? $row->vehicle_id ?? null,
                        'NO_PLAT' => $row->NO_PLAT ?? $row->no_plat ?? '-',
                        'STATUS_DATA' => $row->STATUS_DATA ?? $row->status_data ?? 'ACTIVE',
                    ];
                })->values();

                return (object) [
                    'ID' => $first->ID ?? $first->id ?? null,
                    'USER_ID' => $first->USER_ID ?? $first->user_id ?? null,

                    'NOSTAFF' => $first->NOSTAFF ?? $first->nostaff ?? '-',
                    'NAMA' => $first->NAMA ?? $first->nama ?? '-',
                    'JABATAN' => $first->JABATAN ?? $first->jabatan ?? '-',
                    'KOD_JABATAN' => $first->KOD_JABATAN ?? $first->kod_jabatan ?? '-',

                    'STATUS_DATA' => 'ACTIVE',
                    'VEHICLES' => $vehicles,
                ];
            })
            ->values();

        return view('pages.pengawas.pengawas-list', [
            'title' => 'Senarai Pengawas',
            'pengawas' => $pengawas,
        ]);
    }

    public function create()
    {
        $engineeringUsers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAME"'),
                DB::raw('u.EMAIL as "EMAIL"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->whereNotNull('u.MODULE_ROLE')
            ->whereRaw("
                (
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"ENGINEERING\" && @.role == 48)')
                    OR
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"ENGINEERING\" && @.role == \"48\")')
                )
            ")
            ->whereRaw("UPPER(u.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('u.NAMA')
            ->get();

        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"')
            )
            ->whereNotNull('v.NO_PLAT')
            ->whereRaw("UPPER(v.STATUS_DATA) = 'ACTIVE'")
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('PENGAWAS_VEHICLE as p')
                    ->whereColumn('p.VEHICLE_ID', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->orderBy('v.NO_PLAT')
            ->get();

        return view('pages.pengawas.pengawas-add', [
            'title' => 'Tambah Pengawas',
            'engineeringUsers' => $engineeringUsers,
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'vehicle_id' => 'required|array|min:1',
            'vehicle_id.*' => 'required',
        ], [
            'user_id.required' => 'Sila pilih pengawas.',
            'vehicle_id.required' => 'Sila pilih sekurang-kurangnya satu no plat.',
            'vehicle_id.array' => 'Format pilihan no plat tidak sah.',
            'vehicle_id.min' => 'Sila pilih sekurang-kurangnya satu no plat.',
        ]);

        $user = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.MODULE_ROLE as "MODULE_ROLE"')
            )
            ->where('u.ID', $request->user_id)
            ->whereNotNull('u.MODULE_ROLE')
            ->whereRaw("
                (
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"ENGINEERING\" && @.role == 48)')
                    OR
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"ENGINEERING\" && @.role == \"48\")')
                )
            ")
            ->whereRaw("UPPER(u.STATUS_DATA) = 'ACTIVE'")
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['user_id' => 'Pengguna tidak sah atau bukan role Engineering 48.'])
                ->withInput();
        }

        $vehicleIds = collect($request->vehicle_id)
            ->filter()
            ->unique()
            ->values();

        if ($vehicleIds->isEmpty()) {
            return back()
                ->withErrors(['vehicle_id' => 'Sila pilih sekurang-kurangnya satu no plat.'])
                ->withInput();
        }

        $assignedVehicles = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->select(DB::raw('VEHICLE_ID as "VEHICLE_ID"'))
            ->whereIn('VEHICLE_ID', $vehicleIds->toArray())
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->get()
            ->map(function ($row) {
                return $row->VEHICLE_ID ?? $row->vehicle_id ?? null;
            })
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->toArray();

        if (!empty($assignedVehicles)) {
            return back()
                ->withErrors([
                    'vehicle_id' => 'Terdapat kenderaan yang telah didaftarkan kepada pengawas lain.',
                ])
                ->withInput();
        }

        $insertData = [];

        foreach ($vehicleIds as $vehicleId) {
            $insertData[] = [
                'USER_ID' => $request->user_id,
                'VEHICLE_ID' => $vehicleId,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ];
        }

        DB::connection('oracle')->transaction(function () use ($insertData) {
            DB::connection('oracle')
                ->table('PENGAWAS_VEHICLE')
                ->insert($insertData);
        });

        return redirect()
            ->route('pengawas-list')
            ->with('success', 'Pengawas berjaya ditambah dengan beberapa kenderaan.');
    }

    public function delete($userId)
    {
        $affected = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->where('USER_ID', $userId)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod aktif dijumpai untuk dipadam.');
        }

        return back()->with('success', 'Data pengawas berjaya dipadam.');
    }

    public function activate($userId)
    {
        $affected = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->where('USER_ID', $userId)
            ->whereRaw("UPPER(STATUS_DATA) = 'DELETE'")
            ->update([
                'STATUS_DATA' => 'ACTIVE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        if ($affected === 0) {
            return back()->with('error', 'Tiada rekod dipadam dijumpai untuk diaktifkan semula.');
        }

        return back()->with('success', 'Data pengawas berjaya diaktifkan semula.');
    }

    public function edit($userId)
    {
        $pengawasRows = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE as p')
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->select(
                DB::raw('p.ID as "ID"'),
                DB::raw('p.USER_ID as "USER_ID"'),
                DB::raw('p.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('p.STATUS_DATA as "STATUS_DATA"'),

                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.KOD_JABATAN as "KOD_JABATAN"')
            )
            ->where('p.USER_ID', $userId)
            ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'")
            ->get();

        if ($pengawasRows->isEmpty()) {
            return redirect()
                ->route('pengawas-list')
                ->with('error', 'Rekod pengawas tidak dijumpai.');
        }

        $pengawas = $pengawasRows->first();

        $currentVehicleIds = $pengawasRows
            ->map(function ($row) {
                return $row->VEHICLE_ID
                    ?? $row->vehicle_id
                    ?? null;
            })
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->toArray();

        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"')
            )
            ->whereNotNull('v.NO_PLAT')
            ->whereRaw("UPPER(v.STATUS_DATA) = 'ACTIVE'")
            ->where(function ($query) use ($userId, $currentVehicleIds) {
                $query->whereNotExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('PENGAWAS_VEHICLE as p')
                        ->whereColumn('p.VEHICLE_ID', 'v.ID')
                        ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'")
                        ->where('p.USER_ID', '<>', $userId);
                });

                if (!empty($currentVehicleIds)) {
                    $query->orWhereIn('v.ID', $currentVehicleIds);
                }
            })
            ->orderBy('v.NO_PLAT')
            ->get();

        return view('pages.pengawas.pengawas-edit', [
            'title' => 'Kemaskini Kenderaan Pengawas',
            'pengawas' => $pengawas,
            'vehicles' => $vehicles,
            'currentVehicleIds' => $currentVehicleIds,
        ]);
    }

    public function updateVehicle(Request $request, $userId)
    {
        $request->validate([
            'vehicle_id' => 'required|array|min:1',
            'vehicle_id.*' => 'required',
        ], [
            'vehicle_id.required' => 'Sila pilih sekurang-kurangnya satu no plat.',
            'vehicle_id.array' => 'Format pilihan no plat tidak sah.',
            'vehicle_id.min' => 'Sila pilih sekurang-kurangnya satu no plat.',
        ]);

        $selectedVehicleIds = collect($request->vehicle_id)
            ->filter()
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->values();

        $pengawas = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->where('USER_ID', $userId)
            ->first();

        if (!$pengawas) {
            return redirect()
                ->route('pengawas-list')
                ->with('error', 'Rekod pengawas tidak dijumpai.');
        }

        $assignedToOthers = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE as p')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'p.VEHICLE_ID')
            ->select(
                DB::raw('p.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"')
            )
            ->whereIn('p.VEHICLE_ID', $selectedVehicleIds->toArray())
            ->where('p.USER_ID', '<>', $userId)
            ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'")
            ->get();

        if ($assignedToOthers->isNotEmpty()) {
            $noPlatList = $assignedToOthers
                ->map(function ($row) {
                    return $row->NO_PLAT
                        ?? $row->no_plat
                        ?? null;
                })
                ->filter()
                ->join(', ');

            return back()
                ->withErrors([
                    'vehicle_id' => 'Kenderaan berikut telah didaftarkan kepada pengawas lain: ' . $noPlatList,
                ])
                ->withInput();
        }

        DB::connection('oracle')->transaction(function () use ($userId, $selectedVehicleIds) {

            $currentActiveRows = DB::connection('oracle')
                ->table('PENGAWAS_VEHICLE')
                ->select(
                    DB::raw('ID as "ID"'),
                    DB::raw('VEHICLE_ID as "VEHICLE_ID"')
                )
                ->where('USER_ID', $userId)
                ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
                ->get();

            $currentActiveVehicleIds = $currentActiveRows
                ->map(function ($row) {
                    return $row->VEHICLE_ID
                        ?? $row->vehicle_id
                        ?? null;
                })
                ->filter()
                ->map(fn ($id) => (string) $id)
                ->values();

            $vehicleIdsToRemove = $currentActiveVehicleIds
                ->diff($selectedVehicleIds)
                ->values();

            if ($vehicleIdsToRemove->isNotEmpty()) {
                DB::connection('oracle')
                    ->table('PENGAWAS_VEHICLE')
                    ->where('USER_ID', $userId)
                    ->whereIn('VEHICLE_ID', $vehicleIdsToRemove->toArray())
                    ->update([
                        'STATUS_DATA' => 'DELETE',
                        'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                        'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                    ]);
            }

            foreach ($selectedVehicleIds as $vehicleId) {
                $existingRow = DB::connection('oracle')
                    ->table('PENGAWAS_VEHICLE')
                    ->select(
                        DB::raw('ID as "ID"'),
                        DB::raw('USER_ID as "USER_ID"'),
                        DB::raw('VEHICLE_ID as "VEHICLE_ID"'),
                        DB::raw('STATUS_DATA as "STATUS_DATA"')
                    )
                    ->where('USER_ID', $userId)
                    ->where('VEHICLE_ID', $vehicleId)
                    ->first();

                if ($existingRow) {
                    $existingId = $existingRow->ID
                        ?? $existingRow->id
                        ?? null;

                    if ($existingId) {
                        DB::connection('oracle')
                            ->table('PENGAWAS_VEHICLE')
                            ->where('ID', $existingId)
                            ->update([
                                'STATUS_DATA' => 'ACTIVE',
                                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                            ]);
                    }
                } else {
                    DB::connection('oracle')
                        ->table('PENGAWAS_VEHICLE')
                        ->insert([
                            'USER_ID' => $userId,
                            'VEHICLE_ID' => $vehicleId,
                            'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                            'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                            'STATUS_DATA' => 'ACTIVE',
                        ]);
                }
            }
        });

        return redirect()
            ->route('pengawas-list')
            ->with('success', 'Kenderaan pengawas berjaya dikemaskini.');
    }
}