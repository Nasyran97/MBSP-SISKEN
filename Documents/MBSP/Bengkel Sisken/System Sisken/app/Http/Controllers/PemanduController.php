<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PemanduController extends Controller
{
    public function list()
    {
        $pemandu = DB::connection('oracle')
            ->table('DRIVER as p')
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
                DB::raw('u.NO_TELEFON as "NO_TELEFON"'),

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
                    'NO_TELEFON' => $first->NO_TELEFON ?? $first->no_telefon ?? '-',

                    'STATUS_DATA' => 'ACTIVE',
                    'VEHICLES' => $vehicles,
                ];
            })
            ->values();

        return view('pages.pemandu.pemandu-list', [
            'title' => 'Senarai Pemandu',
            'pemandu' => $pemandu,
        ]);
    }

    public function create()
    {
        $pemanduUsers = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.EMAIL as "EMAIL"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.NO_TELEFON as "NO_TELEFON"'),
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
                    ->from('DRIVER as p')
                    ->whereColumn('p.VEHICLE_ID', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->orderBy('v.NO_PLAT')
            ->get();

        return view('pages.pemandu.pemandu-add', [
            'title' => 'Tambah Pemandu',
            'pemanduUsers' => $pemanduUsers,
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'no_telefon' => ['required', 'string', 'max:50', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'vehicle_id' => 'required|array|min:1',
            'vehicle_id.*' => 'required',
        ], [
            'user_id.required' => 'Sila pilih pemandu.',
            'no_telefon.required' => 'Sila masukkan no telefon pemandu.',
            'no_telefon.regex' => 'Format no telefon tidak sah.',
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
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == 65)')
                    OR
                    JSON_EXISTS(u.MODULE_ROLE, '$[*]?(@.module == \"PENYELENGGARAAN\" && @.role == \"65\")')
                )
            ")
            ->whereRaw("UPPER(u.STATUS_DATA) = 'ACTIVE'")
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['user_id' => 'Pengguna tidak sah atau bukan role Pemandu (65) dalam modul Penyelenggaraan.'])
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
            ->table('DRIVER')
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
                    'vehicle_id' => 'Terdapat kenderaan yang telah didaftarkan kepada pemandu lain.',
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

        DB::connection('oracle')->transaction(function () use ($insertData, $request) {
            DB::connection('oracle')
                ->table('USERS')
                ->where('ID', $request->user_id)
                ->update([
                    'NO_TELEFON' => trim($request->no_telefon),
                    'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                ]);

            DB::connection('oracle')
                ->table('DRIVER')
                ->insert($insertData);
        });

        return redirect()
            ->route('pemandu-list')
            ->with('success', 'Pemandu berjaya ditambah dengan beberapa kenderaan.');
    }

    public function delete($userId)
    {
        $affected = DB::connection('oracle')
            ->table('DRIVER')
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

        return back()->with('success', 'Data pemandu berjaya dipadam.');
    }

    public function activate($userId)
    {
        $affected = DB::connection('oracle')
            ->table('DRIVER')
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

        return back()->with('success', 'Data pemandu berjaya diaktifkan semula.');
    }

    public function edit($userId)
    {
        $pemanduRows = DB::connection('oracle')
            ->table('DRIVER as p')
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->select(
                DB::raw('p.ID as "ID"'),
                DB::raw('p.USER_ID as "USER_ID"'),
                DB::raw('p.VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('p.STATUS_DATA as "STATUS_DATA"'),

                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.KOD_JABATAN as "KOD_JABATAN"'),
                DB::raw('u.NO_TELEFON as "NO_TELEFON"')
            )
            ->where('p.USER_ID', $userId)
            ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'")
            ->get();

        if ($pemanduRows->isEmpty()) {
            return redirect()
                ->route('pemandu-list')
                ->with('error', 'Rekod pemandu tidak dijumpai.');
        }

        $pemandu = $pemanduRows->first();

        $currentVehicleIds = $pemanduRows
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
                        ->from('DRIVER as p')
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

        return view('pages.pemandu.pemandu-edit', [
            'title' => 'Kemaskini Kenderaan Pemandu',
            'pemandu' => $pemandu,
            'vehicles' => $vehicles,
            'currentVehicleIds' => $currentVehicleIds,
        ]);
    }

    public function updateVehicle(Request $request, $userId)
    {
        $request->validate([
            'no_telefon' => ['required', 'string', 'max:50', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'vehicle_id' => 'required|array|min:1',
            'vehicle_id.*' => 'required',
        ], [
            'no_telefon.required' => 'Sila masukkan no telefon pemandu.',
            'no_telefon.regex' => 'Format no telefon tidak sah.',
            'vehicle_id.required' => 'Sila pilih sekurang-kurangnya satu no plat.',
            'vehicle_id.array' => 'Format pilihan no plat tidak sah.',
            'vehicle_id.min' => 'Sila pilih sekurang-kurangnya satu no plat.',
        ]);

        $selectedVehicleIds = collect($request->vehicle_id)
            ->filter()
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->values();

        $pemandu = DB::connection('oracle')
            ->table('DRIVER')
            ->where('USER_ID', $userId)
            ->first();

        if (!$pemandu) {
            return redirect()
                ->route('pemandu-list')
                ->with('error', 'Rekod pemandu tidak dijumpai.');
        }

        $assignedToOthers = DB::connection('oracle')
            ->table('DRIVER as p')
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
                    'vehicle_id' => 'Kenderaan berikut telah didaftarkan kepada pemandu lain: ' . $noPlatList,
                ])
                ->withInput();
        }

        DB::connection('oracle')->transaction(function () use ($userId, $selectedVehicleIds, $request) {
            DB::connection('oracle')
                ->table('USERS')
                ->where('ID', $userId)
                ->update([
                    'NO_TELEFON' => trim($request->no_telefon),
                    'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                ]);

            $currentActiveRows = DB::connection('oracle')
                ->table('DRIVER')
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
                    ->table('DRIVER')
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
                    ->table('DRIVER')
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
                            ->table('DRIVER')
                            ->where('ID', $existingId)
                            ->update([
                                'STATUS_DATA' => 'ACTIVE',
                                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                            ]);
                    }
                } else {
                    DB::connection('oracle')
                        ->table('DRIVER')
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
            ->route('pemandu-list')
            ->with('success', 'Kenderaan pemandu berjaya dikemaskini.');
    }
}