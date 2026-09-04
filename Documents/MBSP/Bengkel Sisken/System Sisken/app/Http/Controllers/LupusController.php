<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LupusController extends Controller
{
    public function lupusCarAdd()
    {
        return view('pages.lupus.lupus-data-add', [
            'title' => 'Tambah Lupus Kenderaan',
        ]);
    }

    public function lupusCarStore(Request $request)
    {
        $request->validate([
            'no_plat' => 'required|string|max:50',
            'pembeli' => 'required|string|max:255',
            'buatan' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'harga_jualan' => 'required|numeric|min:0',

            'tarikh_lupus' => 'nullable|date',
            'kaedah_jualan' => 'required|string|in:TUKAR NAMA,SCRAP',
            'cukai_kastam' => 'required|string|in:YA,TIDAK',
            'tarikh_lelong' => 'nullable|date',
            'status_lupus' => 'required|string|in:AKTIF,TAK AKTIF',
            'tarikh_tukar_milik' => 'nullable|date',
            'tarikh_puspakom' => 'nullable|date',
        ], [
            'no_plat.required' => 'Sila masukkan no plat.',
            'pembeli.required' => 'Sila masukkan pembeli.',
            'buatan.required' => 'Sila masukkan buatan.',
            'model.required' => 'Sila masukkan model.',
            'harga_jualan.required' => 'Sila masukkan harga jualan.',
            'kaedah_jualan.required' => 'Sila pilih kaedah jualan.',
            'cukai_kastam.required' => 'Sila pilih cukai kastam.',
            'status_lupus.required' => 'Sila pilih status.',
        ]);

        DB::connection('oracle')
            ->table('VEHICLE_LUPUS')
            ->insert([
                'NO_PLAT' => strtoupper(trim($request->no_plat)),
                'PEMBELI' => strtoupper(trim($request->pembeli)),
                'BUATAN' => strtoupper(trim($request->buatan)),
                'MODEL' => strtoupper(trim($request->model)),
                'HARGA_JUALAN' => $request->harga_jualan,

                'TARIKH_LUPUS' => $request->tarikh_lupus,
                'KAEDAH_JUALAN' => $request->kaedah_jualan,
                'CUKAI_KASTAM' => $request->cukai_kastam,
                'TARIKH_LELONG' => $request->tarikh_lelong,

                'STATUS_LUPUS' => $request->status_lupus,

                'TARIKH_TUKAR_MILIK' => $request->tarikh_tukar_milik,
                'TARIKH_PUSPAKOM' => $request->tarikh_puspakom,

                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);

        return redirect()
            ->route('lupus-data-add')
            ->with('success', 'Maklumat lupus kenderaan berjaya disimpan.');
    }

        public function list()
    {
        $lupus = DB::connection('oracle')
            ->table('VEHICLE_LUPUS as vl')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'vl.VEHICLE_ID')
            ->select(
                DB::raw('vl.ID as "ID"'),
                DB::raw('vl.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),

                DB::raw('vl.PEMBELI as "PEMBELI"'),
                DB::raw('vl.HARGA_JUALAN as "HARGA_JUALAN"'),
                DB::raw('vl.TARIKH_LUPUS as "TARIKH_LUPUS"'),
                DB::raw('vl.KAEDAH_JUALAN as "KAEDAH_JUALAN"'),
                DB::raw('vl.CUKAI_KASTAM as "CUKAI_KASTAM"'),
                DB::raw('vl.TARIKH_LELONG as "TARIKH_LELONG"'),
                DB::raw('vl.STATUS_LUPUS as "STATUS_LUPUS"'),
                DB::raw('vl.TARIKH_TUKAR_MILIK as "TARIKH_TUKAR_MILIK"'),
                DB::raw('vl.TARIKH_PUSPAKOM as "TARIKH_PUSPAKOM"'),
                DB::raw('vl.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) = 'LUPUS'")
            ->whereRaw("UPPER(vl.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('vl.TARIKH_LUPUS', 'desc')
            ->get();

        return view('pages.lupus.lupus-car-list', [
            'title' => 'Senarai Lupus Kenderaan',
            'lupus' => $lupus,
        ]);
    }

    public function lupusCarEdit($id)
    {
        $lupus = DB::connection('oracle')
            ->table('VEHICLE_LUPUS')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NO_PLAT as "NO_PLAT"'),
                DB::raw('PEMBELI as "PEMBELI"'),
                DB::raw('BUATAN as "BUATAN"'),
                DB::raw('MODEL as "MODEL"'),
                DB::raw('HARGA_JUALAN as "HARGA_JUALAN"'),
                DB::raw('TARIKH_LUPUS as "TARIKH_LUPUS"'),
                DB::raw('KAEDAH_JUALAN as "KAEDAH_JUALAN"'),
                DB::raw('CUKAI_KASTAM as "CUKAI_KASTAM"'),
                DB::raw('TARIKH_LELONG as "TARIKH_LELONG"'),
                DB::raw('STATUS_LUPUS as "STATUS_LUPUS"'),
                DB::raw('TARIKH_TUKAR_MILIK as "TARIKH_TUKAR_MILIK"'),
                DB::raw('TARIKH_PUSPAKOM as "TARIKH_PUSPAKOM"')
            )
            ->where('ID', $id)
            ->first();
    
        if (!$lupus) {
            return redirect()
                ->route('lupus-car-list')
                ->with('error', 'Rekod lupus kenderaan tidak dijumpai.');
        }
    
        return view('pages.lupus.lupus-car-edit', [
            'title' => 'Kemaskini Lupus Kenderaan',
            'lupus' => $lupus,
        ]);
    }

    public function lupusCarUpdate(Request $request, $id)
    {
        $request->validate([
            'no_plat' => 'required|string|max:50',
            'pembeli' => 'required|string|max:255',
            'buatan' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'harga_jualan' => 'required|numeric|min:0',
    
            'tarikh_lupus' => 'nullable|date',
            'kaedah_jualan' => 'required|string|in:TUKAR NAMA,SCRAP',
            'cukai_kastam' => 'required|string|in:YA,TIDAK',
            'tarikh_lelong' => 'nullable|date',
            'status_lupus' => 'required|string|in:AKTIF,TAK AKTIF',
            'tarikh_tukar_milik' => 'nullable|date',
            'tarikh_puspakom' => 'nullable|date',
        ]);
    
        $record = DB::connection('oracle')
            ->table('VEHICLE_LUPUS')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->route('lupus-car-list')
                ->with('error', 'Rekod lupus kenderaan tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_LUPUS')
            ->where('ID', $id)
            ->update([
                'NO_PLAT' => strtoupper(trim($request->no_plat)),
                'PEMBELI' => strtoupper(trim($request->pembeli)),
                'BUATAN' => strtoupper(trim($request->buatan)),
                'MODEL' => strtoupper(trim($request->model)),
                'HARGA_JUALAN' => $request->harga_jualan,
    
                'TARIKH_LUPUS' => $request->tarikh_lupus,
                'KAEDAH_JUALAN' => $request->kaedah_jualan,
                'CUKAI_KASTAM' => $request->cukai_kastam,
                'TARIKH_LELONG' => $request->tarikh_lelong,
    
                'STATUS_LUPUS' => $request->status_lupus,
    
                'TARIKH_TUKAR_MILIK' => $request->tarikh_tukar_milik,
                'TARIKH_PUSPAKOM' => $request->tarikh_puspakom,
    
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->route('lupus-car-list')
            ->with('success', 'Maklumat lupus kenderaan berjaya dikemaskini.');
    }

    public function create($vehicleId)
    {
        $vehicle = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $vehicleId)
            ->first();

        if (!$vehicle) {
            return redirect()
                ->route('rekod-car-list')
                ->with('error', 'Maklumat kenderaan tidak dijumpai.');
        }

        $statusData = strtoupper(trim($vehicle->STATUS_DATA ?? $vehicle->status_data ?? ''));

        if ($statusData === 'LUPUS') {
            return redirect()
                ->route('lupus-car-list')
                ->with('error', 'Kenderaan ini telah dilupuskan.');
        }

        return view('pages.lupus.lupus-data-add', [
            'title' => 'Tambah Lupus Kenderaan',
            'vehicle' => $vehicle,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',

            'pembeli' => 'required|string|max:255',
            'harga_jualan' => 'required|numeric|min:0',
            'tarikh_lupus' => 'nullable|date',
            'kaedah_jualan' => 'required|string|max:50',
            'cukai_kastam' => 'required|string|max:10',
            'tarikh_lelong' => 'nullable|date',
            'tarikh_tukar_milik' => 'nullable|date',
            'tarikh_puspakom' => 'nullable|date',
        ], [
            'vehicle_id.required' => 'Maklumat kenderaan diperlukan.',
            'pembeli.required' => 'Sila masukkan pembeli.',
            'harga_jualan.required' => 'Sila masukkan harga jualan.',
            'harga_jualan.numeric' => 'Harga jualan mesti nombor.',
            'kaedah_jualan.required' => 'Sila pilih kaedah jualan.',
            'cukai_kastam.required' => 'Sila pilih cukai kastam.',
        ]);

        $vehicle = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $request->vehicle_id)
            ->first();

        if (!$vehicle) {
            return back()
                ->withInput()
                ->with('error', 'Maklumat kenderaan tidak dijumpai.');
        }

        DB::connection('oracle')->transaction(function () use ($request) {
            DB::connection('oracle')
                ->table('VEHICLE_LUPUS')
                ->insert([
                    'VEHICLE_ID' => $request->vehicle_id,

                    'PEMBELI' => strtoupper($request->pembeli),
                    'HARGA_JUALAN' => $request->harga_jualan,
                    'TARIKH_LUPUS' => $request->tarikh_lupus,
                    'KAEDAH_JUALAN' => $request->kaedah_jualan,
                    'CUKAI_KASTAM' => $request->cukai_kastam,
                    'TARIKH_LELONG' => $request->tarikh_lelong,
                    'STATUS_LUPUS' => 'AKTIF',
                    'TARIKH_TUKAR_MILIK' => $request->tarikh_tukar_milik,
                    'TARIKH_PUSPAKOM' => $request->tarikh_puspakom,

                    'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                    'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                    'STATUS_DATA' => 'ACTIVE',
                ]);

            DB::connection('oracle')
                ->table('VEHICLE')
                ->where('ID', $request->vehicle_id)
                ->update([
                    'STATUS_DATA' => 'LUPUS',
                    'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                    'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                ]);
        });

        return redirect()
            ->route('lupus-car-list')
            ->with('success', 'Kenderaan berjaya dilupuskan.');
    }

    public function lupusCarList()
    {
        $lupusCars = DB::connection('oracle')
            ->table('VEHICLE_LUPUS as vl')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'vl.VEHICLE_ID')
            ->select(
                DB::raw('vl.ID as "ID"'),
                DB::raw('vl.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),

                DB::raw('vl.PEMBELI as "PEMBELI"'),
                DB::raw('vl.HARGA_JUALAN as "HARGA_JUALAN"'),
                DB::raw('vl.TARIKH_LUPUS as "TARIKH_LUPUS"'),
                DB::raw('vl.KAEDAH_JUALAN as "KAEDAH_JUALAN"'),
                DB::raw('vl.CUKAI_KASTAM as "CUKAI_KASTAM"'),
                DB::raw('vl.TARIKH_LELONG as "TARIKH_LELONG"'),
                DB::raw('vl.STATUS_LUPUS as "STATUS_LUPUS"'),
                DB::raw('vl.TARIKH_TUKAR_MILIK as "TARIKH_TUKAR_MILIK"'),
                DB::raw('vl.TARIKH_PUSPAKOM as "TARIKH_PUSPAKOM"'),
                DB::raw('vl.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) = 'LUPUS'")
            ->whereRaw("UPPER(vl.STATUS_DATA) = 'ACTIVE'")
            ->orderBy('vl.ID', 'desc')
            ->get();

        return view('pages.lupus.lupus-car-list', [
            'title' => 'Senarai Lupus Kenderaan',
            'lupusCars' => $lupusCars,
        ]);
    }
}