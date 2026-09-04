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

class InsuransController extends Controller
{
    public function polisiList()
    {
        $today = now('Asia/Kuala_Lumpur')->startOfDay();
        $currentYear = now('Asia/Kuala_Lumpur')->year;

        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.NO_ENJIN as "NO_ENJIN"'),
                DB::raw('v.NO_CHASIS as "NO_CHASIS"'),
                DB::raw('v.TARIKH_PENDAFTARAN as "TARIKH_PENDAFTARAN"'),
                DB::raw('v.TAHUN_DIBUAT as "TAHUN_DIBUAT"'),
                DB::raw('v.KELAS_KEGUNAAN as "KELAS_KEGUNAAN"')
            )
            ->orderBy('v.NO_PLAT')
            ->get()
            ->map(function ($vehicle) use ($today, $currentYear) {

                $vehicleId = $vehicle->VEHICLE_ID
                    ?? $vehicle->vehicle_id
                    ?? $vehicle->ID
                    ?? $vehicle->id
                    ?? null;

                $tahunDibuat = $vehicle->TAHUN_DIBUAT
                    ?? $vehicle->tahun_dibuat
                    ?? null;

                $vehicle->USIA = !empty($tahunDibuat)
                    ? ($currentYear - (int) $tahunDibuat)
                    : null;

                if (!$vehicleId) {
                    $vehicle->POLICIES = collect();
                    $vehicle->CURRENT_POLICY = null;
                    $vehicle->HAS_FUTURE_POLICY = false;

                    $vehicle->POLISI_ID = null;
                    $vehicle->PANEL_ID = null;
                    $vehicle->PANEL_NAME = null;
                    $vehicle->JENIS_POLISI = null;
                    $vehicle->NCD = null;
                    $vehicle->TARIKH_MULA_INSURANS = null;
                    $vehicle->TARIKH_TAMAT_INSURANS = null;
                    $vehicle->POLISI_STATUS = null;

                    return $vehicle;
                }

                $policies = DB::connection('oracle')
                    ->table('INSURANS_POLISI as ip')
                    ->leftJoin('INSURANS_PANEL as p', 'p.ID', '=', 'ip.PANEL_ID')
                    ->select(
                        DB::raw('ip.ID as "ID"'),
                        DB::raw('ip.VEHICLE_ID as "VEHICLE_ID"'),
                        DB::raw('ip.PANEL_ID as "PANEL_ID"'),
                        DB::raw('ip.NO_POLISI as "NO_POLISI"'),
                        DB::raw('ip.TARIKH_MULA_INSURANS as "TARIKH_MULA_INSURANS"'),
                        DB::raw('ip.TARIKH_TAMAT_INSURANS as "TARIKH_TAMAT_INSURANS"'),
                        DB::raw('ip.JENIS_POLISI as "JENIS_POLISI"'),
                        DB::raw('ip.NCD as "NCD"'),
                        DB::raw('ip.STATUS_DATA as "STATUS_DATA"'),
                        DB::raw('ip.POLISI_STATUS as "POLISI_STATUS"'),
                        DB::raw('p.PANEL_NAME as "PANEL_NAME"')
                    )
                    ->where('ip.VEHICLE_ID', $vehicleId)
                    ->whereRaw("(UPPER(ip.STATUS_DATA) = 'ACTIVE' OR ip.STATUS_DATA IS NULL)")
                    ->orderBy('ip.TARIKH_MULA_INSURANS', 'desc')
                    ->get();

                foreach ($policies as $policy) {
                    $policyId = $policy->ID
                        ?? $policy->id
                        ?? null;

                    $startDate = $policy->TARIKH_MULA_INSURANS
                        ?? $policy->tarikh_mula_insurans
                        ?? null;

                    $endDate = $policy->TARIKH_TAMAT_INSURANS
                        ?? $policy->tarikh_tamat_insurans
                        ?? null;

                    $start = !empty($startDate)
                        ? Carbon::parse($startDate)->startOfDay()
                        : null;

                    $end = !empty($endDate)
                        ? Carbon::parse($endDate)->startOfDay()
                        : null;

                    if ($start && $today->lt($start)) {
                        $policyStatus = 'AKAN AKTIF';
                    } elseif ($end && $today->gt($end)) {
                        $policyStatus = 'TAMAT TEMPOH';
                    } elseif ($end && $today->lte($end) && $today->copy()->addMonth()->gte($end)) {
                        $policyStatus = 'AKAN TAMAT TEMPOH';
                    } else {
                        $policyStatus = 'AKTIF';
                    }

                    $currentDbStatus = $policy->POLISI_STATUS
                        ?? $policy->polisi_status
                        ?? null;

                    if ($policyId && $currentDbStatus !== $policyStatus) {
                        DB::connection('oracle')
                            ->table('INSURANS_POLISI')
                            ->where('ID', $policyId)
                            ->update([
                                'POLISI_STATUS' => $policyStatus,
                                'STATUS_DATA' => 'ACTIVE',
                                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                            ]);
                    }

                    $policy->POLISI_STATUS = $policyStatus;
                }

                $currentPolicy = $policies->first(function ($policy) {
                    $status = $policy->POLISI_STATUS
                        ?? $policy->polisi_status
                        ?? null;

                    return in_array($status, [
                        'AKTIF',
                        'AKAN TAMAT TEMPOH',
                    ]);
                });

                $futurePolicy = $policies->first(function ($policy) {
                    $status = $policy->POLISI_STATUS
                        ?? $policy->polisi_status
                        ?? null;

                    return $status === 'AKAN AKTIF';
                });

                $latestPolicy = $currentPolicy ?? $futurePolicy ?? $policies->first();

                $vehicle->POLICIES = $policies;
                $vehicle->CURRENT_POLICY = $latestPolicy;
                $vehicle->HAS_FUTURE_POLICY = $futurePolicy ? true : false;

                $vehicle->POLISI_ID = $latestPolicy->ID
                    ?? $latestPolicy->id
                    ?? null;

                $vehicle->PANEL_ID = $latestPolicy->PANEL_ID
                    ?? $latestPolicy->panel_id
                    ?? null;

                $vehicle->PANEL_NAME = $latestPolicy->PANEL_NAME
                    ?? $latestPolicy->panel_name
                    ?? null;

                $vehicle->JENIS_POLISI = $latestPolicy->JENIS_POLISI
                    ?? $latestPolicy->jenis_polisi
                    ?? null;

                $vehicle->NCD = $latestPolicy->NCD
                    ?? $latestPolicy->ncd
                    ?? null;

                $vehicle->TARIKH_MULA_INSURANS = $latestPolicy->TARIKH_MULA_INSURANS
                    ?? $latestPolicy->tarikh_mula_insurans
                    ?? null;

                $vehicle->TARIKH_TAMAT_INSURANS = $latestPolicy->TARIKH_TAMAT_INSURANS
                    ?? $latestPolicy->tarikh_tamat_insurans
                    ?? null;

                $vehicle->POLISI_STATUS = $latestPolicy->POLISI_STATUS
                    ?? $latestPolicy->polisi_status
                    ?? null;

                return $vehicle;
            });

        $panels = DB::connection('oracle')
            ->table('INSURANS_PANEL')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('PANEL_NAME as "PANEL_NAME"')
            )
            ->where('STATUS_DATA', 'ACTIVE')
            ->orderBy('PANEL_NAME')
            ->get();

        return view('pages.insurans.insurans-polisi-list', [
            'title' => 'Senarai Polisi Insurans',
            'vehicles' => $vehicles,
            'panels' => $panels,
        ]);
    }

    public function polisiStore(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'panel_id' => 'required',
            'jenis_polisi' => 'required|string|max:100',
            'tarikh_mula_insurans' => 'required|date_format:d-m-Y',
            'tarikh_tamat_insurans' => 'required|date_format:d-m-Y',
            'ncd' => 'nullable|numeric|min:0|max:100',
        ]);

        $vehicle = DB::connection('oracle')
        ->table('VEHICLE')
            ->where('ID', $request->vehicle_id)
            ->first();

        if (!$vehicle) {
            return redirect()
                ->back()
                ->with('error', 'Kenderaan tidak dijumpai.');
        }

        $futurePolicyExists = DB::connection('oracle')
        ->table('INSURANS_POLISI')
            ->where('VEHICLE_ID', $request->vehicle_id)
            ->where('STATUS_DATA', 'ACTIVE')
            ->where('POLISI_STATUS', 'AKAN AKTIF')
            ->exists();

        if ($futurePolicyExists) {
            return redirect()
                ->back()
                ->with('error', 'Polisi akan aktif telah wujud. Sila semak maklumat polisi.');
        }

        $today = now('Asia/Kuala_Lumpur')->startOfDay();

        $start = Carbon::createFromFormat('d-m-Y', $request->tarikh_mula_insurans)->startOfDay();

        $end = Carbon::createFromFormat('d-m-Y', $request->tarikh_tamat_insurans)->startOfDay();

        if ($today->lt($start)) {
            $polisiStatus = 'AKAN AKTIF';
        } elseif ($today->gt($end)) {
            $polisiStatus = 'TAMAT TEMPOH';
        } elseif ($today->lte($end) && $today->copy()->addMonth()->gte($end)) {
            $polisiStatus = 'AKAN TAMAT TEMPOH';
        } else {
            $polisiStatus = 'AKTIF';
        }

        DB::connection('oracle')
        ->table('INSURANS_POLISI')->insert([
            'VEHICLE_ID'              => $request->vehicle_id,
            'PANEL_ID'                => $request->panel_id,
            'JENIS_POLISI'            => $request->jenis_polisi,
            'NCD'                     => $request->ncd,
            'TARIKH_MULA_INSURANS'    => DB::raw("TO_DATE('" . $start->format('Y-m-d') . "', 'YYYY-MM-DD')"),
            'TARIKH_TAMAT_INSURANS'   => DB::raw("TO_DATE('" . $end->format('Y-m-d') . "', 'YYYY-MM-DD')"),
            'POLISI_STATUS'           => $polisiStatus,
            'CREATED_BY'              => session('nostaff') ?? 'SYSTEM',
            'CREATED_AT'              => now('Asia/Kuala_Lumpur'),
            'STATUS_DATA'             => 'ACTIVE',
        ]);

        return redirect()
            ->route('insurans-polisi-list')
            ->with('success', 'Maklumat polisi insurans berjaya disimpan.');
    }

    public function create()
    {
        $departments = [];

        try {
            $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department', [
                    '6874be6b13269' => 'XHWBIEWPB0UKNLO505Q2B171'
                ]);

            $json = $response->json();
            $departments = $json['data'] ?? $json ?? [];
        } catch (\Exception $e) {
            $departments = [];
        }

        $today = now('Asia/Kuala_Lumpur')->startOfDay();

        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.JABATAN as "JABATAN"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('v.LOKASI_PENEMPATAN as "LOKASI_PENEMPATAN"'),
            )
            ->whereNotNull('v.NO_PLAT')
            ->orderBy('v.NO_PLAT')
            ->get()
            ->map(function ($vehicle) use ($today) {
                $vehicleId = $vehicle->VEHICLE_ID
                    ?? $vehicle->vehicle_id
                    ?? null;

                $policies = DB::connection('oracle')
                    ->table('INSURANS_POLISI as ip')
                    ->leftJoin('INSURANS_PANEL as p', 'p.ID', '=', 'ip.PANEL_ID')
                    ->select(
                        DB::raw('ip.ID as "ID"'),
                        DB::raw('ip.VEHICLE_ID as "VEHICLE_ID"'),
                        DB::raw('ip.PANEL_ID as "PANEL_ID"'),
                        DB::raw('ip.NO_POLISI as "NO_POLISI"'),
                        DB::raw('ip.TARIKH_MULA_INSURANS as "TARIKH_MULA_INSURANS"'),
                        DB::raw('ip.TARIKH_TAMAT_INSURANS as "TARIKH_TAMAT_INSURANS"'),
                        DB::raw('ip.JENIS_POLISI as "JENIS_POLISI"'),
                        DB::raw('ip.NCD as "NCD"'),
                        DB::raw('ip.STATUS_DATA as "STATUS_DATA"'),
                        DB::raw('ip.POLISI_STATUS as "POLISI_STATUS"'),
                        DB::raw('p.PANEL_NAME as "PANEL_NAME"')
                    )
                    ->where('ip.VEHICLE_ID', $vehicleId)
                    ->whereRaw("(UPPER(ip.STATUS_DATA) = 'ACTIVE' OR ip.STATUS_DATA IS NULL)")
                    ->orderBy('ip.TARIKH_MULA_INSURANS', 'desc')
                    ->get();

                foreach ($policies as $policy) {
                    $policyId = $policy->ID ?? $policy->id ?? null;

                    $startDate = $policy->TARIKH_MULA_INSURANS
                        ?? $policy->tarikh_mula_insurans
                        ?? null;

                    $endDate = $policy->TARIKH_TAMAT_INSURANS
                        ?? $policy->tarikh_tamat_insurans
                        ?? null;

                    $start = !empty($startDate)
                        ? Carbon::parse($startDate)->startOfDay()
                        : null;

                    $end = !empty($endDate)
                        ? Carbon::parse($endDate)->startOfDay()
                        : null;

                    if ($start && $today->lt($start)) {
                        $policyStatus = 'AKAN AKTIF';
                    } elseif ($end && $today->gt($end)) {
                        $policyStatus = 'TAMAT TEMPOH';
                    } elseif ($end && $today->lte($end) && $today->copy()->addMonth()->gte($end)) {
                        $policyStatus = 'AKAN TAMAT TEMPOH';
                    } else {
                        $policyStatus = 'AKTIF';
                    }

                    $currentDbStatus = $policy->POLISI_STATUS
                        ?? $policy->polisi_status
                        ?? null;

                    if ($policyId && $currentDbStatus !== $policyStatus) {
                        DB::connection('oracle')
                            ->table('INSURANS_POLISI')
                            ->where('ID', $policyId)
                            ->update([
                                'POLISI_STATUS' => $policyStatus,
                                'STATUS_DATA' => 'ACTIVE',
                                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                            ]);
                    }

                    $policy->POLISI_STATUS = $policyStatus;
                }

                $currentPolicy = $policies->first(function ($policy) {
                    $status = strtoupper(trim(
                        $policy->POLISI_STATUS
                        ?? $policy->polisi_status
                        ?? ''
                    ));
                
                    return in_array($status, [
                        'AKTIF',
                        'AKAN TAMAT TEMPOH'
                    ]);
                });
                
                $vehicle->CURRENT_POLICY = $currentPolicy;
                $vehicle->CURRENT_NCD = $currentPolicy
                    ? ($currentPolicy->NCD ?? $currentPolicy->ncd ?? null)
                    : null;

                return $vehicle;
            });

        $tuntutanPihakKetiga = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->select(
                DB::raw('TUNTUTAN_PIHAK_KETIGA_NAME as "TUNTUTAN_PIHAK_KETIGA_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_PIHAK_KETIGA_NAME')
            ->get();
        
        $lokasiPenempatan = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('LOKASI_PENEMPATAN_NAME as "LOKASI_PENEMPATAN_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('LOKASI_PENEMPATAN_NAME')
            ->get();

        return view('pages.insurans.insurans-data-add', [
            'title' => 'Tambah Data Insurans',
            'departments' => $departments,
            'vehicles' => $vehicles,
            'tuntutanPihakKetiga' => $tuntutanPihakKetiga,
            'lokasiPenempatan' => $lokasiPenempatan,
        ]);
    }

    public function staffByDepartment(Request $request)
    {
        $request->validate([
            'kodjabatanstaff' => 'required'
        ]);

        try {
            $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/staff/bydepartment', [
                    '633bb3e5a0993' => 'LBTXN72WW3JCS13W1WIUE485',
                    'kodjabatanstaff' => $request->kodjabatanstaff
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal mendapatkan senarai staf.'
                ], 500);
            }

            $json = $response->json();
            $staffs = $json['data'] ?? $json ?? [];

            return response()->json([
                'status' => true,
                'data' => $staffs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Ralat sambungan API staf.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'kod_jabatan_staff' => 'required',
            'no_pekerja' => 'required',
            'nama' => 'required',
            'jawatan' => 'required',
        ]);

        DB::connection('oracle')
        ->table('INSURANS')->insert([
            'VEHICLE_ID' => $request->vehicle_id,

            'JENIS_KENDERAAN' => $request->jenis_kenderaan,
            'JABATAN_KENDERAAN' => $request->jabatan_kenderaan,
        ]);

        return redirect()
            ->route('insurans-panel-add')
            ->with('success', 'Data insurans berjaya disimpan.');
    }

    public function panelList()
    {
        $panels = DB::connection('oracle')
        ->table('INSURANS_PANEL')
            ->orderBy('PANEL_NAME')
            ->get();

        return view('pages.panel-insurans.insurans-panel-list', [
            'title' => 'Senarai Panel Insurans',
            'panels' => $panels
        ]);
    }

    public function activatePanel($id)
    {
        DB::connection('oracle')
            ->table('INSURANS_PANEL')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'ACTIVE',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                'UPDATED_BY'  => session('nostaff'),
            ]);

        return redirect()
            ->route('insurans-panel-list')
            ->with('success', 'Panel insurans berjaya diaktifkan.');
    }

    public function deactivatePanel($id)
    {
        DB::connection('oracle')
            ->table('INSURANS_PANEL')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'INACTIVE',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
                'UPDATED_BY'  => session('nostaff'),
            ]);

        return redirect()
            ->route('insurans-panel-list')
            ->with('success', 'Panel insurans berjaya dinyahaktifkan.');
    }

    public function panelAdd()
    {
        return view('pages.panel-insurans.insurans-panel-add', [
            'title' => 'Tambah Panel Insurans'
        ]);
    }

    public function panelStore(Request $request)
    {
        $request->validate([
            'panel_name' => 'required|string|max:255',
        ], [
            'panel_name.required' => 'Nama panel insurans wajib diisi.',
            'panel_name.max' => 'Nama panel insurans tidak boleh melebihi 255 aksara.',
        ]);

        $panelName = trim($request->panel_name);

        $exists = DB::connection('oracle')
            ->table('INSURANS_PANEL')
            ->whereRaw('UPPER(TRIM(PANEL_NAME)) = ?', [strtoupper($panelName)])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Panel insurans ini telah wujud.');
        }

        DB::connection('oracle')
            ->table('INSURANS_PANEL')->insert([
            'PANEL_NAME'  => $panelName,
            'CREATED_BY'  => session('nostaff'),
            'CREATED_AT'  => now('Asia/Kuala_Lumpur'),
            'STATUS_DATA' => 'ACTIVE',
        ]);

        return redirect()
            ->route('insurans-panel-list')
            ->with('success', 'Panel insurans berjaya ditambah.');
    }

    public function storeKemalangan(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',

            'no_plat' => 'nullable|string|max:50',

            'kod_jabatan_staff' => 'required|string|max:50',
            'desc_jabatan_staff' => 'nullable|string|max:255',

            'no_pekerja' => 'required|string|max:50',
            'nama' => 'nullable|string|max:255',
            'jawatan' => 'nullable|string|max:255',

            'jabatan_kenderaan' => 'nullable|string|max:255',
            'jenis_kenderaan' => 'nullable|string|max:255',

            'ncd' => 'nullable|numeric|min:0|max:100',

            'baik_pulih_bengkel' => 'nullable|string|max:20',
            'jenis_tuntutan_insurans' => 'nullable|string|max:100',
            'bahagian' => 'nullable|string|max:100',
            'daerah' => 'nullable|string|max:50',

            'tarikh_kemalangan' => 'nullable|date',
            'lokasi_kemalangan' => 'nullable|string|max:500',
            'keterangan_kemalangan' => 'nullable|string',

            'kos_baik_pulih' => 'nullable|numeric|min:0',
            'kos_tuntutan_pihak_ketiga' => 'nullable|numeric|min:0',

            'nama_penuntut' => 'nullable|string|max:255',
            'no_plat_pihak_ketiga' => 'nullable|string|max:50',
            'jenis_kenderaan_pihak_ketiga' => 'nullable|string|max:255',
            'kerosakan_pihak_ketiga' => 'nullable|string',
            'jenis_tuntutan_pihak_ketiga' => 'nullable|string|max:100',
            'no_telefon_pihak_ketiga' => 'nullable|string|max:50',

            'tarikh_hantar_kes' => 'nullable|date',
            'tuntutan_gantirugi' => 'nullable|string|max:100',
            'status_kes' => 'nullable|string|max:100',
            'keputusan_kes_trafik' => 'nullable|string|max:100',
            'kemajuan_semasa' => 'nullable|string',
            'laporan_adjuster_insurans' => 'nullable|string',
            'lampiran_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'vehicle_id.required' => 'Sila pilih kenderaan.',
            'kod_jabatan_staff.required' => 'Sila pilih jabatan.',
            'no_pekerja.required' => 'Sila pilih no pekerja / pemandu.',

            'ncd.numeric' => 'NCD mestilah nombor.',
            'ncd.min' => 'NCD tidak boleh kurang daripada 0.',
            'ncd.max' => 'NCD tidak boleh melebihi 100.',

            'tarikh_kemalangan.date' => 'Format tarikh kemalangan tidak sah.',
            'tarikh_hantar_kes.date' => 'Format tarikh hantar kes tidak sah.',

            'kos_baik_pulih.numeric' => 'Kos baik pulih mestilah nombor.',
            'kos_tuntutan_pihak_ketiga.numeric' => 'Kos tuntutan pihak ketiga mestilah nombor.',

            'lampiran_file.file' => 'Lampiran mesti dalam format fail.',
            'lampiran_file.mimes' => 'Lampiran hanya dibenarkan dalam format PDF, JPG, JPEG atau PNG.',
            'lampiran_file.max' => 'Saiz lampiran tidak boleh melebihi 5MB.',
        ]);

        $vehicle = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $request->vehicle_id)
            ->first();

        if (!$vehicle) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Maklumat kenderaan tidak dijumpai.');
        }

        $lampiranFile = null;

        if ($request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');

            $fileName = 'laporan_adjuster_kemalangan_' .
                $request->vehicle_id . '_' .
                now('Asia/Kuala_Lumpur')->format('YmdHis') . '_' .
                Str::random(8) . '.' .
                strtolower($file->getClientOriginalExtension());

            $folder = 'Laporan Adjuster Kemalangan';

            $lampiranFile = $folder . '/' . $fileName;

            Storage::disk('synology')->putFileAs(
                $folder,
                $file,
                $fileName
            );
        }

        DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->insert([
                'VEHICLE_ID'                    => $request->vehicle_id,
                'NO_PLAT'                       => $request->no_plat,

                'KOD_JABATAN_STAF'              => $request->kod_jabatan_staff,
                'DESC_JABATAN_STAF'             => $request->desc_jabatan_staff,

                'NO_PEKERJA'                    => $request->no_pekerja,
                'NAMA'                          => $request->nama,
                'JAWATAN'                       => $request->jawatan,

                'JABATAN_KENDERAAN'             => $request->jabatan_kenderaan,
                'JENIS_KENDERAAN'               => $request->jenis_kenderaan,

                'NCD_SEMASA'                    => $request->ncd,

                'BAIK_PULIH_BENGKEL'            => $request->baik_pulih_bengkel,
                'JENIS_TUNTUTAN_INSURANS'       => $request->jenis_tuntutan_insurans,
                'BAHAGIAN'                      => $request->bahagian,
                'DAERAH'                        => $request->daerah,

                'TARIKH_KEMALANGAN'             => $request->tarikh_kemalangan,
                'LOKASI_KEMALANGAN'             => $request->lokasi_kemalangan,
                'KETERANGAN_KEMALANGAN'         => $request->keterangan_kemalangan,

                'KOS_BAIK_PULIH'                => $request->kos_baik_pulih,
                'KOS_TUNTUTAN_PIHAK_KETIGA'     => $request->kos_tuntutan_pihak_ketiga,

                'NAMA_PENUNTUT'                 => $request->nama_penuntut,
                'NO_PLAT_PIHAK_KETIGA'          => $request->no_plat_pihak_ketiga,
                'JENIS_KENDERAAN_PIHAK_KETIGA'  => $request->jenis_kenderaan_pihak_ketiga,
                'KEROSAKAN_PIHAK_KETIGA'        => $request->kerosakan_pihak_ketiga,
                'JENIS_TUNTUTAN_PIHAK_KETIGA'   => $request->jenis_tuntutan_pihak_ketiga,
                'NO_TELEFON_PIHAK_KETIGA'       => $request->no_telefon_pihak_ketiga,

                'TARIKH_HANTAR_KES'             => $request->tarikh_hantar_kes,
                'TUNTUTAN_GANTIRUGI'            => $request->tuntutan_gantirugi,
                'STATUS_KES'                    => $request->status_kes,
                'KEPUTUSAN_KES_TRAFIK'          => $request->keputusan_kes_trafik,
                'KEMAJUAN_SEMASA'               => $request->kemajuan_semasa,
                'LAPORAN_ADJUSTER_INSURANS'     => $request->laporan_adjuster_insurans,
                'LAMPIRAN_FILE'                 => $lampiranFile,

                'CREATED_BY'                    => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT'                    => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA'                   => 'ACTIVE',
            ]);

        return redirect()
            ->back()
            ->with('success', 'Data kemalangan kenderaan berjaya disimpan.');
    }

    public function kemalanganList()
    {
        $kemalangan = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN as vk')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'vk.VEHICLE_ID')
            ->select(
                DB::raw('vk.ID as "ID"'),
                DB::raw('vk.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('vk.NO_PLAT as "NO_PLAT_KEMALANGAN"'),
                DB::raw('v.NO_PLAT as "NO_PLAT_VEHICLE"'),

                DB::raw('vk.KOD_JABATAN_STAF as "KOD_JABATAN_STAF"'),
                DB::raw('vk.DESC_JABATAN_STAF as "DESC_JABATAN_STAF"'),

                DB::raw('vk.NO_PEKERJA as "NO_PEKERJA"'),
                DB::raw('vk.NAMA as "NAMA"'),
                DB::raw('vk.JAWATAN as "JAWATAN"'),

                DB::raw('vk.JABATAN_KENDERAAN as "JABATAN_KENDERAAN"'),
                DB::raw('v.JABATAN as "JABATAN_VEHICLE"'),
                DB::raw('vk.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('vk.NCD_SEMASA as "NCD_SEMASA"'),

                DB::raw('vk.BAIK_PULIH_BENGKEL as "BAIK_PULIH_BENGKEL"'),
                DB::raw('vk.JENIS_TUNTUTAN_INSURANS as "JENIS_TUNTUTAN_INSURANS"'),
                DB::raw('vk.BAHAGIAN as "BAHAGIAN"'),
                DB::raw('vk.DAERAH as "DAERAH"'),

                DB::raw('vk.TARIKH_KEMALANGAN as "TARIKH_KEMALANGAN"'),
                DB::raw('vk.LOKASI_KEMALANGAN as "LOKASI_KEMALANGAN"'),
                DB::raw('vk.KETERANGAN_KEMALANGAN as "KETERANGAN_KEMALANGAN"'),

                DB::raw('vk.KOS_BAIK_PULIH as "KOS_BAIK_PULIH"'),
                DB::raw('vk.KOS_TUNTUTAN_PIHAK_KETIGA as "KOS_TUNTUTAN_PIHAK_KETIGA"'),

                DB::raw('vk.NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('vk.NO_PLAT_PIHAK_KETIGA as "NO_PLAT_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_KENDERAAN_PIHAK_KETIGA as "JENIS_KENDERAAN_PIHAK_KETIGA"'),
                DB::raw('vk.KEROSAKAN_PIHAK_KETIGA as "KEROSAKAN_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_TUNTUTAN_PIHAK_KETIGA as "JENIS_TUNTUTAN_PIHAK_KETIGA"'),
                DB::raw('vk.NO_TELEFON_PIHAK_KETIGA as "NO_TELEFON_PIHAK_KETIGA"'),
                DB::raw('vk.TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('vk.TUNTUTAN_GANTIRUGI as "TUNTUTAN_GANTIRUGI"'),
                DB::raw('vk.STATUS_KES as "STATUS_KES"'),
                DB::raw('vk.KEPUTUSAN_KES_TRAFIK as "KEPUTUSAN_KES_TRAFIK"'),
                DB::raw('vk.KEMAJUAN_SEMASA as "KEMAJUAN_SEMASA"'),
                DB::raw('vk.LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),
                DB::raw('vk.LAMPIRAN_FILE as "LAMPIRAN_FILE"'),

                DB::raw('vk.CREATED_BY as "CREATED_BY"'),
                DB::raw('vk.CREATED_AT as "CREATED_AT"'),
                DB::raw('vk.UPDATED_BY as "UPDATED_BY"'),
                DB::raw('vk.UPDATED_AT as "UPDATED_AT"'),
                DB::raw('vk.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(vk.STATUS_DATA) = 'ACTIVE' OR vk.STATUS_DATA IS NULL)")
            ->orderBy('vk.TARIKH_KEMALANGAN', 'desc')
            ->orderBy('vk.ID', 'desc')
            ->get();
        return view('pages.insurans.insurans-kemalangan-list', [
            'title' => 'Senarai Kemalangan Insurans',
            'kemalangan' => $kemalangan,
        ]);
    }

    public function updateKemalanganStatus(Request $request, $id)
    {
        $request->validate([
            'status_kes' => 'nullable|string|max:100',
            'keputusan_kes_trafik' => 'nullable|string|max:100',
            'kemajuan_semasa' => 'nullable|string',
            'laporan_adjuster_insurans' => 'nullable|string',
            'lampiran_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'lampiran_file.file' => 'Lampiran mesti dalam format fail.',
            'lampiran_file.mimes' => 'Lampiran hanya dibenarkan dalam format PDF, JPG, JPEG atau PNG.',
            'lampiran_file.max' => 'Saiz lampiran tidak boleh melebihi 5MB.',
        ]);

        $record = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod kemalangan tidak dijumpai.');
        }

        $updateData = [
            'STATUS_KES' => $request->status_kes,
            'KEPUTUSAN_KES_TRAFIK' => $request->keputusan_kes_trafik,
            'KEMAJUAN_SEMASA' => $request->kemajuan_semasa,
            'LAPORAN_ADJUSTER_INSURANS' => $request->laporan_adjuster_insurans,
            'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
            'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
        ];

        if ($request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');

            $folder = 'Laporan Adjuster Kemalangan';

            $fileName = 'laporan_adjuster_kemalangan_' .
                $id . '_' .
                now('Asia/Kuala_Lumpur')->format('YmdHis') . '_' .
                Str::random(8) . '.' .
                strtolower($file->getClientOriginalExtension());

            Storage::disk('synology')->putFileAs(
                $folder,
                $file,
                $fileName
            );

            $newLampiranPath = $folder . '/' . $fileName;

            $oldLampiranPath = $record->LAMPIRAN_FILE
                ?? $record->lampiran_file
                ?? null;

            if (!empty($oldLampiranPath) && Storage::disk('synology')->exists($oldLampiranPath)) {
                Storage::disk('synology')->delete($oldLampiranPath);
            }

            $updateData['LAMPIRAN_FILE'] = $newLampiranPath;
        }

        DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->update($updateData);

        return redirect()
            ->back()
            ->with('success', 'Maklumat status kes dan lampiran berjaya dikemaskini.');
    }

    public function gantirugiList()
    {
        $gantirugi = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN as vk')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'vk.VEHICLE_ID')
            ->select(
                DB::raw('vk.ID as "ID"'),
                DB::raw('vk.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('vk.NO_PLAT as "NO_PLAT_KEMALANGAN"'),
                DB::raw('v.NO_PLAT as "NO_PLAT_VEHICLE"'),

                DB::raw('vk.KOD_JABATAN_STAF as "KOD_JABATAN_STAF"'),
                DB::raw('vk.DESC_JABATAN_STAF as "DESC_JABATAN_STAF"'),
                DB::raw('vk.NO_PEKERJA as "NO_PEKERJA"'),
                DB::raw('vk.NAMA as "NAMA"'),
                DB::raw('vk.JAWATAN as "JAWATAN"'),

                DB::raw('vk.JABATAN_KENDERAAN as "JABATAN_KENDERAAN"'),
                DB::raw('v.JABATAN as "JABATAN_VEHICLE"'),
                DB::raw('vk.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('vk.NCD_SEMASA as "NCD_SEMASA"'),

                DB::raw('vk.BAIK_PULIH_BENGKEL as "BAIK_PULIH_BENGKEL"'),
                DB::raw('vk.JENIS_TUNTUTAN_INSURANS as "JENIS_TUNTUTAN_INSURANS"'),
                DB::raw('vk.BAHAGIAN as "BAHAGIAN"'),
                DB::raw('vk.DAERAH as "DAERAH"'),

                DB::raw('vk.TARIKH_KEMALANGAN as "TARIKH_KEMALANGAN"'),
                DB::raw('vk.LOKASI_KEMALANGAN as "LOKASI_KEMALANGAN"'),
                DB::raw('vk.KETERANGAN_KEMALANGAN as "KETERANGAN_KEMALANGAN"'),

                DB::raw('vk.KOS_BAIK_PULIH as "KOS_BAIK_PULIH"'),
                DB::raw('vk.KOS_TUNTUTAN_PIHAK_KETIGA as "KOS_TUNTUTAN_PIHAK_KETIGA"'),

                DB::raw('vk.NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('vk.NO_PLAT_PIHAK_KETIGA as "NO_PLAT_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_KENDERAAN_PIHAK_KETIGA as "JENIS_KENDERAAN_PIHAK_KETIGA"'),
                DB::raw('vk.KEROSAKAN_PIHAK_KETIGA as "KEROSAKAN_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_TUNTUTAN_PIHAK_KETIGA as "JENIS_TUNTUTAN_PIHAK_KETIGA"'),
                DB::raw('vk.NO_TELEFON_PIHAK_KETIGA as "NO_TELEFON_PIHAK_KETIGA"'),

                DB::raw('vk.TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('vk.TUNTUTAN_GANTIRUGI as "TUNTUTAN_GANTIRUGI"'),
                DB::raw('vk.STATUS_KES as "STATUS_KES"'),
                DB::raw('vk.KEPUTUSAN_KES_TRAFIK as "KEPUTUSAN_KES_TRAFIK"'),
                DB::raw('vk.KEMAJUAN_SEMASA as "KEMAJUAN_SEMASA"'),
                DB::raw('vk.LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),

                DB::raw('vk.STATUS_GANTIRUGI as "STATUS_GANTIRUGI"'),
                DB::raw('vk.STATUS_REPAIR as "STATUS_REPAIR"'),

                DB::raw('vk.CREATED_BY as "CREATED_BY"'),
                DB::raw('vk.CREATED_AT as "CREATED_AT"'),
                DB::raw('vk.UPDATED_BY as "UPDATED_BY"'),
                DB::raw('vk.UPDATED_AT as "UPDATED_AT"'),
                DB::raw('vk.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(vk.STATUS_DATA) = 'ACTIVE' OR vk.STATUS_DATA IS NULL)")
            ->orderBy('vk.TARIKH_KEMALANGAN', 'desc')
            ->orderBy('vk.ID', 'desc')
            ->get();

        return view('pages.insurans.insurans-gantirugi-list', [
            'title' => 'Senarai Gantirugi',
            'gantirugi' => $gantirugi,
        ]);
    }

    public function updateGantirugiStatus(Request $request, $id)
    {
        $request->validate([
            'status_gantirugi' => 'required|string|in:DALAM PROSES,SELESAI',
        ], [
            'status_gantirugi.required' => 'Sila pilih status gantirugi.',
            'status_gantirugi.in' => 'Status gantirugi tidak sah.',
        ]);

        $record = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->update([
                'STATUS_GANTIRUGI' => $request->status_gantirugi,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Status gantirugi berjaya dikemaskini.');
    }

    public function repairList()
    {
        $repair = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN as vk')
            ->leftJoin('VEHICLE as v', 'v.ID', '=', 'vk.VEHICLE_ID')
            ->select(
                DB::raw('vk.ID as "ID"'),
                DB::raw('vk.VEHICLE_ID as "VEHICLE_ID"'),

                DB::raw('vk.NO_PLAT as "NO_PLAT_KEMALANGAN"'),
                DB::raw('v.NO_PLAT as "NO_PLAT_VEHICLE"'),

                DB::raw('vk.KOD_JABATAN_STAF as "KOD_JABATAN_STAF"'),
                DB::raw('vk.DESC_JABATAN_STAF as "DESC_JABATAN_STAF"'),
                DB::raw('vk.NO_PEKERJA as "NO_PEKERJA"'),
                DB::raw('vk.NAMA as "NAMA"'),
                DB::raw('vk.JAWATAN as "JAWATAN"'),

                DB::raw('vk.JABATAN_KENDERAAN as "JABATAN_KENDERAAN"'),
                DB::raw('v.JABATAN as "JABATAN_VEHICLE"'),
                DB::raw('vk.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('vk.NCD_SEMASA as "NCD_SEMASA"'),

                DB::raw('vk.BAIK_PULIH_BENGKEL as "BAIK_PULIH_BENGKEL"'),
                DB::raw('vk.JENIS_TUNTUTAN_INSURANS as "JENIS_TUNTUTAN_INSURANS"'),
                DB::raw('vk.BAHAGIAN as "BAHAGIAN"'),
                DB::raw('vk.DAERAH as "DAERAH"'),

                DB::raw('vk.TARIKH_KEMALANGAN as "TARIKH_KEMALANGAN"'),
                DB::raw('vk.LOKASI_KEMALANGAN as "LOKASI_KEMALANGAN"'),
                DB::raw('vk.KETERANGAN_KEMALANGAN as "KETERANGAN_KEMALANGAN"'),

                DB::raw('vk.KOS_BAIK_PULIH as "KOS_BAIK_PULIH"'),
                DB::raw('vk.KOS_TUNTUTAN_PIHAK_KETIGA as "KOS_TUNTUTAN_PIHAK_KETIGA"'),

                DB::raw('vk.NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('vk.NO_PLAT_PIHAK_KETIGA as "NO_PLAT_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_KENDERAAN_PIHAK_KETIGA as "JENIS_KENDERAAN_PIHAK_KETIGA"'),
                DB::raw('vk.KEROSAKAN_PIHAK_KETIGA as "KEROSAKAN_PIHAK_KETIGA"'),
                DB::raw('vk.JENIS_TUNTUTAN_PIHAK_KETIGA as "JENIS_TUNTUTAN_PIHAK_KETIGA"'),
                DB::raw('vk.NO_TELEFON_PIHAK_KETIGA as "NO_TELEFON_PIHAK_KETIGA"'),

                DB::raw('vk.TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('vk.TUNTUTAN_GANTIRUGI as "TUNTUTAN_GANTIRUGI"'),
                DB::raw('vk.STATUS_KES as "STATUS_KES"'),
                DB::raw('vk.KEPUTUSAN_KES_TRAFIK as "KEPUTUSAN_KES_TRAFIK"'),
                DB::raw('vk.KEMAJUAN_SEMASA as "KEMAJUAN_SEMASA"'),
                DB::raw('vk.LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),

                DB::raw('vk.STATUS_REPAIR as "STATUS_REPAIR"'),

                DB::raw('vk.CREATED_BY as "CREATED_BY"'),
                DB::raw('vk.CREATED_AT as "CREATED_AT"'),
                DB::raw('vk.UPDATED_BY as "UPDATED_BY"'),
                DB::raw('vk.UPDATED_AT as "UPDATED_AT"'),
                DB::raw('vk.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(vk.STATUS_DATA) = 'ACTIVE' OR vk.STATUS_DATA IS NULL)")
            ->orderBy('vk.TARIKH_KEMALANGAN', 'desc')
            ->orderBy('vk.ID', 'desc')
            ->get();

        return view('pages.insurans.insurans-repair-list', [
            'title' => 'Senarai Baik Pulih',
            'repair' => $repair,
        ]);
    }

    public function updateRepairStatus(Request $request, $id)
    {
        $request->validate([
            'status_repair' => 'required|string|in:DALAM PROSES,SELESAI,TOTAL LOST',
            'kos_baik_pulih' => 'nullable|numeric|min:0',
        ], [
            'status_repair.required' => 'Sila pilih status repair.',
            'status_repair.in' => 'Status repair tidak sah.',
            'kos_baik_pulih.numeric' => 'Kos baik pulih mesti dalam format nombor.',
            'kos_baik_pulih.min' => 'Kos baik pulih tidak boleh kurang daripada 0.',
        ]);

        $record = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->update([
                'STATUS_REPAIR' => $request->status_repair,
                'KOS_BAIK_PULIH' => $request->filled('kos_baik_pulih')
                    ? $request->kos_baik_pulih
                    : null,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Status repair dan kos baik pulih berjaya dikemaskini.');
    }

    public function kemalanganLampiran($id)
    {
        $record = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            abort(404, 'Rekod tidak dijumpai.');
        }

        $lampiranFile = $record->LAMPIRAN_FILE
            ?? $record->lampiran_file
            ?? null;

        if (!$lampiranFile) {
            abort(404, 'Fail tidak dijumpai.');
        }

        if (!Storage::disk('synology')->exists($lampiranFile)) {
            abort(404, 'Fail tidak dijumpai di server.');
        }

        $fileContent = Storage::disk('synology')->get($lampiranFile);
        $mimeType = Storage::disk('synology')->mimeType($lampiranFile) ?? 'application/octet-stream';

        return response($fileContent, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($lampiranFile) . '"');
    }
}