<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MahkamahController extends Controller
{
    public function tuntutanMahkamahList()
    {
        $tuntutanMahkamah = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->orderByRaw("
                CASE 
                    WHEN UPPER(STATUS_DATA) = 'ACTIVE' THEN 1
                    WHEN UPPER(STATUS_DATA) = 'DELETE' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('TUNTUTAN_NAME')
            ->get();

        return view('pages.tuntutan.mahkamah-tuntutan-list', [
            'title' => 'Senarai Jenis Tuntutan Mahkamah',
            'tuntutanMahkamah' => $tuntutanMahkamah,
        ]);
    }

    public function tuntutanMahkamahAdd()
    {
        return view('pages.tuntutan.mahkamah-tuntutan-add', [
            'title' => 'Tambah Jenis Tuntutan Mahkamah',
        ]);
    }

    public function tuntutanMahkamahStore(Request $request)
    {
        $request->validate([
            'tuntutan_name' => 'required|string|max:255',
        ], [
            'tuntutan_name.required' => 'Sila masukkan nama jenis tuntutan mahkamah.',
        ]);

        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->whereRaw('UPPER(TUNTUTAN_NAME) = ?', [strtoupper($request->tuntutan_name)])
            ->first();

        if ($exists) {
            $status = strtoupper($exists->STATUS_DATA ?? $exists->status_data ?? '');

            if ($status === 'DELETE') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jenis tuntutan mahkamah ini telah wujud tetapi berstatus DELETE. Sila aktifkan semula di senarai.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan mahkamah ini telah wujud.');
        }

        DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->insert([
                'TUNTUTAN_NAME' => strtoupper(trim($request->tuntutan_name)),
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);

        return redirect()
            ->route('mahkamah-tuntutan-list')
            ->with('success', 'Jenis tuntutan mahkamah berjaya ditambah.');
    }

    public function tuntutanMahkamahUpdate(Request $request, $id)
    {
        $request->validate([
            'tuntutan_name' => 'required|string|max:255',
        ], [
            'tuntutan_name.required' => 'Sila masukkan nama jenis tuntutan mahkamah.',
        ]);

        $record = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->whereRaw('UPPER(TUNTUTAN_NAME) = ?', [strtoupper($request->tuntutan_name)])
            ->where('ID', '<>', $id)
            ->first();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan mahkamah ini telah wujud.');
        }

        DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->update([
                'TUNTUTAN_NAME' => strtoupper(trim($request->tuntutan_name)),
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan mahkamah berjaya dikemaskini.');
    }

    public function tuntutanMahkamahDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan mahkamah berjaya dipadam.');
    }

    public function tuntutanMahkamahActivate($id)
    {
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'ACTIVE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan mahkamah berjaya diaktifkan semula.');
    }

    public function mahkamahDataAdd()
    {
        $jenisTuntutanMahkamah = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_NAME')
            ->get();
    
        $departments = [];
    
        try {
            $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department', [
                    '6874be6b13269' => 'XHWBIEWPB0UKNLO505Q2B171'
                ]);
    
            $departments = $response->json()['data'] ?? $response->json();
        } catch (\Exception $e) {
            $departments = [];
        }
    
        $today = now('Asia/Kuala_Lumpur')->startOfDay();
    
        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"')
            )
            ->whereNotNull('v.NO_PLAT')
            ->orderBy('v.NO_PLAT')
            ->get()
            ->map(function ($vehicle) use ($today) {
                $vehicleId = $vehicle->VEHICLE_ID ?? $vehicle->vehicle_id ?? null;
    
                $policy = DB::connection('oracle')
                    ->table('INSURANS_POLISI as ip')
                    ->leftJoin('INSURANS_PANEL as p', 'p.ID', '=', 'ip.PANEL_ID')
                    ->select(
                        DB::raw('ip.ID as "POLISI_ID"'),
                        DB::raw('ip.PANEL_ID as "PANEL_ID"'),
                        DB::raw('p.PANEL_NAME as "PANEL_NAME"'),
                        DB::raw('ip.TARIKH_MULA_INSURANS as "TARIKH_MULA_INSURANS"'),
                        DB::raw('ip.TARIKH_TAMAT_INSURANS as "TARIKH_TAMAT_INSURANS"'),
                        DB::raw('ip.POLISI_STATUS as "POLISI_STATUS"')
                    )
                    ->where('ip.VEHICLE_ID', $vehicleId)
                    ->whereRaw("(UPPER(ip.STATUS_DATA) = 'ACTIVE' OR ip.STATUS_DATA IS NULL)")
                    ->where(function ($query) use ($today) {
                        $query->whereDate('ip.TARIKH_MULA_INSURANS', '<=', $today)
                            ->whereDate('ip.TARIKH_TAMAT_INSURANS', '>=', $today);
                    })
                    ->orderBy('ip.TARIKH_MULA_INSURANS', 'desc')
                    ->first();
    
                $vehicle->CURRENT_PANEL_ID = $policy->PANEL_ID ?? $policy->panel_id ?? '';
                $vehicle->CURRENT_PANEL_NAME = $policy->PANEL_NAME ?? $policy->panel_name ?? '';
    
                return $vehicle;
            });
    
        return view('pages.mahkamah.mahkamah-data-add', [
            'title' => 'Tambah Kes Mahkamah',
            'jenisTuntutanMahkamah' => $jenisTuntutanMahkamah,
            'departments' => $departments,
            'vehicles' => $vehicles,
        ]);
    }

    public function mahkamahDataStore(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'no_kes_mahkamah' => 'required|string|max:100',
            'peguam_plaintif' => 'nullable|string|max:255',
            'peguam_mbsp' => 'nullable|string|max:255',
            'nama_penuntut' => 'required|string|max:255',
            'no_kenderaan_jenis' => 'nullable|string|max:255',
            'penyata_tuntutan' => 'nullable|string',

            'tuntutan_mahkamah_id' => 'required',
            'vehicle_id' => 'nullable',

            'no_plat' => 'nullable|string|max:50',
            'syarikat_insurans_id' => 'nullable',
            'syarikat_insurans' => 'nullable|string|max:255',

            'jabatan_kod' => 'required|string|max:50',
            'jabatan_nama' => 'nullable|string|max:255',

            'no_pekerja' => 'required|string|max:50',
            'nama_staf' => 'nullable|string|max:255',
            'jawatan_staf' => 'nullable|string|max:255',

            'daerah_kejadian' => 'required|string|max:50',

            'status_perbicaraan' => 'nullable|string|max:255',
            'tarikh_bicara_seterusnya' => 'nullable|date',
            'keputusan_kes' => 'nullable|string|max:255',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tahun.required' => 'Sila masukkan tahun.',
            'tahun.digits' => 'Tahun mestilah 4 digit.',
            'no_kes_mahkamah.required' => 'Sila masukkan no kes mahkamah.',
            'nama_penuntut.required' => 'Sila masukkan nama penuntut.',
            'tuntutan_mahkamah_id.required' => 'Sila pilih jenis tuntutan.',
            'vehicle_id.required' => 'Sila pilih no plat kenderaan.',
            'jabatan_kod.required' => 'Sila pilih jabatan.',
            'no_pekerja.required' => 'Sila pilih nama staf.',
            'daerah_kejadian.required' => 'Sila pilih daerah kejadian.',
            'file_upload.mimes' => 'Format fail mestilah PDF, JPG, JPEG atau PNG.',
            'file_upload.max' => 'Saiz fail maksimum 5MB.',
        ]);

        $jenisTuntutan = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $request->tuntutan_mahkamah_id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if (!$jenisTuntutan) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan mahkamah tidak dijumpai atau tidak aktif.');
        }

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

        $tuntutanName = $jenisTuntutan->TUNTUTAN_NAME
            ?? $jenisTuntutan->tuntutan_name
            ?? null;

        $syarikatInsurans = $request->syarikat_insurans;

        if ($syarikatInsurans === 'Kenderaan ini tiada insurans') {
            $syarikatInsurans = null;
        }

        $fileUploadPath = null;

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');

            $folder = 'Tuntutan Perundangan/' . now('Asia/Kuala_Lumpur')->format('Y') . '/' . now('Asia/Kuala_Lumpur')->format('m');

            $noKesSlug = Str::slug($request->no_kes_mahkamah) ?: 'kes-mahkamah';
            $extension = strtolower($file->getClientOriginalExtension());

            $newFileName = now('Asia/Kuala_Lumpur')->format('Ymd_His') . '_' . $noKesSlug . '.' . $extension;

            Storage::disk('synology')->putFileAs($folder, $file, $newFileName);

            $fileUploadPath = $folder . '/' . $newFileName;
        }

        DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->insert([
                'TAHUN' => $request->tahun,
                'NO_KES_MAHKAMAH' => strtoupper(trim($request->no_kes_mahkamah)),

                'PEGUAM_PLAINTIF' => $request->peguam_plaintif
                    ? strtoupper(trim($request->peguam_plaintif))
                    : null,

                'PEGUAM_MBSP' => $request->peguam_mbsp
                    ? strtoupper(trim($request->peguam_mbsp))
                    : null,

                'NAMA_PENUNTUT' => strtoupper(trim($request->nama_penuntut)),

                'NO_KENDERAAN_JENIS' => $request->no_kenderaan_jenis
                    ? strtoupper(trim($request->no_kenderaan_jenis))
                    : null,

                'PENYATA_TUNTUTAN' => $request->penyata_tuntutan,

                'TUNTUTAN_MAHKAMAH_ID' => $request->tuntutan_mahkamah_id,
                'JENIS_TUNTUTAN' => $tuntutanName,

                'VEHICLE_ID' => $request->vehicle_id,
                'NO_PLAT' => strtoupper(trim($request->no_plat ?? '')),

                'SYARIKAT_INSURANS_ID' => $request->syarikat_insurans_id,
                'SYARIKAT_INSURANS' => $syarikatInsurans,

                'JABATAN_KOD' => $request->jabatan_kod,
                'JABATAN_NAMA' => $request->jabatan_nama,

                'NO_PEKERJA' => $request->no_pekerja,
                'NAMA_STAF' => $request->nama_staf,
                'JAWATAN_STAF' => $request->jawatan_staf,

                'DAERAH_KEJADIAN' => $request->daerah_kejadian,

                'STATUS_PERBICARAAN' => $request->status_perbicaraan,
                'TARIKH_BICARA_SETERUSNYA' => $request->tarikh_bicara_seterusnya,
                'KEPUTUSAN_KES' => $request->keputusan_kes,

                'FILE_UPLOAD' => $fileUploadPath,

                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);

        return redirect()
            ->route('mahkamah-data-add')
            ->with('success', 'Kes mahkamah berjaya disimpan.');
    }

    public function staffByDepartment(Request $request)
    {
        Log::info('MAHKAMAH STAFF REQUEST RECEIVED', [
            'request_all' => $request->all(),
            'kodjabatanstaff' => $request->input('kodjabatanstaff'),
        ]);

        $request->validate([
            'kodjabatanstaff' => 'required',
        ]);

        $payload = [
            '633bb3e5a0993' => 'LBTXN72WW3JCS13W1WIUE485',
            'kodjabatanstaff' => $request->kodjabatanstaff,
        ];

        Log::info('MAHKAMAH STAFF API PAYLOAD SENT', [
            'endpoint' => 'https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/staff/bydepartment',
            'payload' => [
                '633bb3e5a0993' => '***hidden***',
                'kodjabatanstaff' => $request->kodjabatanstaff,
            ],
        ]);

        try {
            $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_633bb3e5a094b/staff/bydepartment', $payload);

            $json = $response->json();

            Log::info('MAHKAMAH STAFF API RAW RESPONSE', [
                'http_status' => $response->status(),
                'successful' => $response->successful(),
                'json' => $json,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => 'API staff gagal diakses.',
                ], 500);
            }

            if (($json['status'] ?? null) === 'error') {
                Log::error('MAHKAMAH STAFF API RETURNED ERROR', [
                    'department_code' => $request->kodjabatanstaff,
                    'message' => $json['message'] ?? null,
                ]);

                return response()->json([
                    'status' => false,
                    'data' => [],
                    'message' => $json['message'] ?? 'API staff returned error.',
                ], 422);
            }

            $staffs = $json['data'] ?? [];

            if (!is_array($staffs)) {
                $staffs = [];
            }

            $staffLog = [];

            foreach ($staffs as $staff) {
                if (!is_array($staff)) {
                    continue;
                }

                $staffLog[] = [
                    'nostaff' => $staff['NOSTAFF']
                        ?? $staff['nostaff']
                        ?? $staff['NO_STAFF']
                        ?? $staff['no_staff']
                        ?? null,

                    'nama' => $staff['NAMASTAFF']
                        ?? $staff['namastaff']
                        ?? $staff['NAMA']
                        ?? $staff['nama']
                        ?? null,

                    'jawatan' => $staff['JAWATANSTAFF']
                        ?? $staff['jawatanstaff']
                        ?? $staff['JAWATAN']
                        ?? $staff['jawatan']
                        ?? null,
                ];
            }

            Log::info('MAHKAMAH STAFF API PARSED STAFF LIST', [
                'department_code' => $request->kodjabatanstaff,
                'total_staff' => count($staffs),
                'staff_list' => $staffLog,
            ]);

            return response()->json([
                'status' => true,
                'data' => $staffs,
            ]);
        } catch (\Exception $e) {
            Log::error('MAHKAMAH STAFF API ERROR', [
                'department_code' => $request->kodjabatanstaff,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'Ralat semasa ambil data staf.',
            ], 500);
        }
    }

    private function getMahkamahKesByStatus(string $status)
    {
        return DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TAHUN as "TAHUN"'),
                DB::raw('DBMS_LOB.SUBSTR(NO_KES_MAHKAMAH, 4000, 1) as "NO_KES_MAHKAMAH"'),
                DB::raw('PEGUAM_PLAINTIF as "PEGUAM_PLAINTIF"'),
                DB::raw('PEGUAM_MBSP as "PEGUAM_MBSP"'),
                DB::raw('NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('NO_KENDERAAN_JENIS as "NO_KENDERAAN_JENIS"'),
                DB::raw('DBMS_LOB.SUBSTR(PENYATA_TUNTUTAN, 4000, 1) as "PENYATA_TUNTUTAN"'),
                DB::raw('TUNTUTAN_MAHKAMAH_ID as "TUNTUTAN_MAHKAMAH_ID"'),
                DB::raw('JENIS_TUNTUTAN as "JENIS_TUNTUTAN"'),
                DB::raw('VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('NO_PLAT as "NO_PLAT"'),
                DB::raw('SYARIKAT_INSURANS_ID as "SYARIKAT_INSURANS_ID"'),
                DB::raw('SYARIKAT_INSURANS as "SYARIKAT_INSURANS"'),
                DB::raw('JABATAN_KOD as "JABATAN_KOD"'),
                DB::raw('JABATAN_NAMA as "JABATAN_NAMA"'),
                DB::raw('NO_PEKERJA as "NO_PEKERJA"'),
                DB::raw('NAMA_STAF as "NAMA_STAF"'),
                DB::raw('JAWATAN_STAF as "JAWATAN_STAF"'),
                DB::raw('DAERAH_KEJADIAN as "DAERAH_KEJADIAN"'),
                DB::raw('STATUS_PERBICARAAN as "STATUS_PERBICARAAN"'),
                DB::raw('TARIKH_BICARA_SETERUSNYA as "TARIKH_BICARA_SETERUSNYA"'),
                DB::raw('KEPUTUSAN_KES as "KEPUTUSAN_KES"'),
                DB::raw('FILE_UPLOAD as "FILE_UPLOAD"'),
                DB::raw('STATUS_KES as "STATUS_KES"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereRaw("UPPER(STATUS_KES) = ?", [strtoupper($status)])
            ->orderBy('ID', 'desc')
            ->get();
    }

    public function mahkamahKesDalamProsesList()
    {
        $kesMahkamah = $this->getMahkamahKesByStatus('DALAM PROSES');

        return view('pages.mahkamah.mahkamah-kes-list', [
            'title' => 'Senarai Kes Mahkamah - Dalam Proses',
            'description' => 'Senarai rekod kes mahkamah yang masih dalam proses perbicaraan.',
            'kesMahkamah' => $kesMahkamah,
            'canEdit' => true,
            'currentStatus' => 'DALAM PROSES',
        ]);
    }

    public function mahkamahKesSelesaiList()
    {
        $kesMahkamah = $this->getMahkamahKesByStatus('SELESAI PERBICARAAN');

        return view('pages.mahkamah.mahkamah-kes-list', [
            'title' => 'Senarai Kes Mahkamah - Selesai Perbicaraan',
            'description' => 'Senarai rekod kes mahkamah yang telah selesai perbicaraan.',
            'kesMahkamah' => $kesMahkamah,
            'canEdit' => false,
            'currentStatus' => 'SELESAI PERBICARAAN',
        ]);
    }

    public function mahkamahKesEdit($id)
    {
        $kes = DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TAHUN as "TAHUN"'),
                DB::raw('DBMS_LOB.SUBSTR(NO_KES_MAHKAMAH, 4000, 1) as "NO_KES_MAHKAMAH"'),
                DB::raw('PEGUAM_PLAINTIF as "PEGUAM_PLAINTIF"'),
                DB::raw('PEGUAM_MBSP as "PEGUAM_MBSP"'),
                DB::raw('NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('NO_KENDERAAN_JENIS as "NO_KENDERAAN_JENIS"'),
                DB::raw('DBMS_LOB.SUBSTR(PENYATA_TUNTUTAN, 4000, 1) as "PENYATA_TUNTUTAN"'),
                DB::raw('TUNTUTAN_MAHKAMAH_ID as "TUNTUTAN_MAHKAMAH_ID"'),
                DB::raw('JENIS_TUNTUTAN as "JENIS_TUNTUTAN"'),
                DB::raw('VEHICLE_ID as "VEHICLE_ID"'),
                DB::raw('NO_PLAT as "NO_PLAT"'),
                DB::raw('SYARIKAT_INSURANS_ID as "SYARIKAT_INSURANS_ID"'),
                DB::raw('SYARIKAT_INSURANS as "SYARIKAT_INSURANS"'),
                DB::raw('JABATAN_KOD as "JABATAN_KOD"'),
                DB::raw('JABATAN_NAMA as "JABATAN_NAMA"'),
                DB::raw('NO_PEKERJA as "NO_PEKERJA"'),
                DB::raw('NAMA_STAF as "NAMA_STAF"'),
                DB::raw('JAWATAN_STAF as "JAWATAN_STAF"'),
                DB::raw('DAERAH_KEJADIAN as "DAERAH_KEJADIAN"'),
                DB::raw('STATUS_PERBICARAAN as "STATUS_PERBICARAAN"'),
                DB::raw('TARIKH_BICARA_SETERUSNYA as "TARIKH_BICARA_SETERUSNYA"'),
                DB::raw('KEPUTUSAN_KES as "KEPUTUSAN_KES"'),
                DB::raw('FILE_UPLOAD as "FILE_UPLOAD"'),
                DB::raw('STATUS_KES as "STATUS_KES"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->where('ID', $id)
            ->first();
    
        if (!$kes) {
            return redirect()
                ->route('mahkamah-kes-list')
                ->with('error', 'Rekod kes mahkamah tidak dijumpai.');
        }
    
        $jenisTuntutanMahkamah = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_NAME')
            ->get();
    
        $departments = [];
    
        try {
            $response = Http::withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department', [
                    '6874be6b13269' => 'XHWBIEWPB0UKNLO505Q2B171'
                ]);
    
            $departments = $response->json()['data'] ?? $response->json();
        } catch (\Exception $e) {
            $departments = [];
        }
    
        $today = now('Asia/Kuala_Lumpur')->startOfDay();
    
        $vehicles = DB::connection('oracle')
            ->table('VEHICLE as v')
            ->select(
                DB::raw('v.ID as "VEHICLE_ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"')
            )
            ->whereNotNull('v.NO_PLAT')
            ->orderBy('v.NO_PLAT')
            ->get()
            ->map(function ($vehicle) use ($today) {
                $vehicleId = $vehicle->VEHICLE_ID ?? $vehicle->vehicle_id ?? null;
    
                $policy = DB::connection('oracle')
                    ->table('INSURANS_POLISI as ip')
                    ->leftJoin('INSURANS_PANEL as p', 'p.ID', '=', 'ip.PANEL_ID')
                    ->select(
                        DB::raw('ip.ID as "POLISI_ID"'),
                        DB::raw('ip.PANEL_ID as "PANEL_ID"'),
                        DB::raw('p.PANEL_NAME as "PANEL_NAME"'),
                        DB::raw('ip.TARIKH_MULA_INSURANS as "TARIKH_MULA_INSURANS"'),
                        DB::raw('ip.TARIKH_TAMAT_INSURANS as "TARIKH_TAMAT_INSURANS"'),
                        DB::raw('ip.POLISI_STATUS as "POLISI_STATUS"')
                    )
                    ->where('ip.VEHICLE_ID', $vehicleId)
                    ->whereRaw("(UPPER(ip.STATUS_DATA) = 'ACTIVE' OR ip.STATUS_DATA IS NULL)")
                    ->where(function ($query) use ($today) {
                        $query->whereDate('ip.TARIKH_MULA_INSURANS', '<=', $today)
                            ->whereDate('ip.TARIKH_TAMAT_INSURANS', '>=', $today);
                    })
                    ->orderBy('ip.TARIKH_MULA_INSURANS', 'desc')
                    ->first();
    
                $vehicle->CURRENT_PANEL_ID = $policy->PANEL_ID ?? $policy->panel_id ?? '';
                $vehicle->CURRENT_PANEL_NAME = $policy->PANEL_NAME ?? $policy->panel_name ?? '';
    
                return $vehicle;
            });
    
        return view('pages.mahkamah.mahkamah-kes-edit', [
            'title' => 'Kemaskini Kes Mahkamah',
            'kes' => $kes,
            'jenisTuntutanMahkamah' => $jenisTuntutanMahkamah,
            'departments' => $departments,
            'vehicles' => $vehicles,
        ]);
    }

    public function mahkamahKesUpdate(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'no_kes_mahkamah' => 'required|string',
            'peguam_plaintif' => 'nullable|string|max:255',
            'peguam_mbsp' => 'nullable|string|max:255',
            'nama_penuntut' => 'required|string|max:255',
            'no_kenderaan_jenis' => 'nullable|string|max:255',
            'penyata_tuntutan' => 'nullable|string',
            'tuntutan_mahkamah_id' => 'required',
            'vehicle_id' => 'required',
            'no_plat' => 'nullable|string|max:50',
            'syarikat_insurans_id' => 'nullable',
            'syarikat_insurans' => 'nullable|string|max:255',
            'jabatan_kod' => 'required|string|max:50',
            'jabatan_nama' => 'nullable|string|max:255',
            'no_pekerja' => 'required|string|max:50',
            'nama_staf' => 'nullable|string|max:255',
            'jawatan_staf' => 'nullable|string|max:255',
            'daerah_kejadian' => 'required|string|max:50',
            'status_perbicaraan' => 'nullable|string|max:255',
            'tarikh_bicara_seterusnya' => 'nullable|date',
            'keputusan_kes' => 'nullable|string|max:255',
            'status_kes' => 'required|string|in:DALAM PROSES,SELESAI PERBICARAAN',
            'file_upload' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tahun.required' => 'Sila masukkan tahun.',
            'tahun.digits' => 'Tahun mestilah 4 digit.',
            'no_kes_mahkamah.required' => 'Sila masukkan no kes mahkamah.',
            'nama_penuntut.required' => 'Sila masukkan nama penuntut.',
            'tuntutan_mahkamah_id.required' => 'Sila pilih jenis tuntutan.',
            'vehicle_id.required' => 'Sila pilih no plat kenderaan.',
            'jabatan_kod.required' => 'Sila pilih jabatan.',
            'no_pekerja.required' => 'Sila pilih nama staf.',
            'daerah_kejadian.required' => 'Sila pilih daerah kejadian.',
            'status_kes.required' => 'Sila pilih status kes.',
            'file_upload.mimes' => 'Format fail mestilah PDF, JPG, JPEG atau PNG.',
            'file_upload.max' => 'Saiz fail maksimum 5MB.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->route('mahkamah-kes-list')
                ->with('error', 'Rekod kes mahkamah tidak dijumpai.');
        }
    
        $jenisTuntutan = DB::connection('oracle')
            ->table('TUNTUTAN_MAHKAMAH')
            ->where('ID', $request->tuntutan_mahkamah_id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if (!$jenisTuntutan) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan mahkamah tidak dijumpai atau tidak aktif.');
        }
    
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
    
        $tuntutanName = $jenisTuntutan->TUNTUTAN_NAME
            ?? $jenisTuntutan->tuntutan_name
            ?? null;
    
        $syarikatInsurans = $request->syarikat_insurans;
    
        if ($syarikatInsurans === 'Kenderaan ini tiada insurans') {
            $syarikatInsurans = null;
        }

        $fileUploadPath = $record->FILE_UPLOAD ?? $record->file_upload ?? null;

        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');

            $folder = 'Tuntutan Perundangan/' . now('Asia/Kuala_Lumpur')->format('Y') . '/' . now('Asia/Kuala_Lumpur')->format('m');

            $noKesSlug = Str::slug($request->no_kes_mahkamah) ?: 'kes-mahkamah';
            $extension = strtolower($file->getClientOriginalExtension());

            $newFileName = now('Asia/Kuala_Lumpur')->format('Ymd_His') . '_' . $noKesSlug . '.' . $extension;

            Storage::disk('synology')->putFileAs($folder, $file, $newFileName);

            $oldFile = $fileUploadPath;

            if ($oldFile && Storage::disk('synology')->exists($oldFile)) {
                Storage::disk('synology')->delete($oldFile);
            }

            $fileUploadPath = $folder . '/' . $newFileName;
        }

        DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->where('ID', $id)
            ->update([
                'TAHUN' => $request->tahun,
                'NO_KES_MAHKAMAH' => strtoupper(trim($request->no_kes_mahkamah)),
    
                'PEGUAM_PLAINTIF' => $request->peguam_plaintif
                    ? strtoupper(trim($request->peguam_plaintif))
                    : null,
    
                'PEGUAM_MBSP' => $request->peguam_mbsp
                    ? strtoupper(trim($request->peguam_mbsp))
                    : null,
    
                'NAMA_PENUNTUT' => strtoupper(trim($request->nama_penuntut)),
    
                'NO_KENDERAAN_JENIS' => $request->no_kenderaan_jenis
                    ? strtoupper(trim($request->no_kenderaan_jenis))
                    : null,
    
                'PENYATA_TUNTUTAN' => $request->penyata_tuntutan,
    
                'TUNTUTAN_MAHKAMAH_ID' => $request->tuntutan_mahkamah_id,
                'JENIS_TUNTUTAN' => $tuntutanName,
    
                'VEHICLE_ID' => $request->vehicle_id,
                'NO_PLAT' => strtoupper(trim($request->no_plat ?? '')),
    
                'SYARIKAT_INSURANS_ID' => $request->syarikat_insurans_id,
                'SYARIKAT_INSURANS' => $syarikatInsurans,
    
                'JABATAN_KOD' => $request->jabatan_kod,
                'JABATAN_NAMA' => $request->jabatan_nama,
    
                'NO_PEKERJA' => $request->no_pekerja,
                'NAMA_STAF' => $request->nama_staf,
                'JAWATAN_STAF' => $request->jawatan_staf,
    
                'DAERAH_KEJADIAN' => $request->daerah_kejadian,
    
                'STATUS_PERBICARAAN' => $request->status_perbicaraan,
                'TARIKH_BICARA_SETERUSNYA' => $request->tarikh_bicara_seterusnya,
                'KEPUTUSAN_KES' => $request->keputusan_kes,
                'STATUS_KES' => $request->status_kes,

                'FILE_UPLOAD' => $fileUploadPath,

                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        if ($request->status_kes === 'SELESAI PERBICARAAN') {
            return redirect()
                ->route('mahkamah-kes-selesai-list')
                ->with('success', 'Kes mahkamah berjaya dikemaskini.');
        }
        
        return redirect()
            ->route('mahkamah-kes-list')
            ->with('success', 'Kes mahkamah berjaya dikemaskini.');
    }

    public function mahkamahKesFile($id)
    {
        $record = DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            abort(404, 'Rekod tidak dijumpai.');
        }

        $filePath = $record->FILE_UPLOAD ?? $record->file_upload ?? null;

        if (!$filePath) {
            abort(404, 'Fail tidak dijumpai.');
        }

        if (!Storage::disk('synology')->exists($filePath)) {
            abort(404, 'Fail tidak dijumpai di server.');
        }

        $mimeType = Storage::disk('synology')->mimeType($filePath) ?? 'application/octet-stream';

        return response(Storage::disk('synology')->get($filePath), 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"');
    }

    public function printMahkamahKesPdf($id)
    {
        $kes = DB::connection('oracle')
            ->table('MAHKAMAH_KES')
            ->where('ID', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if (!$kes) {
            return back()->with('error', 'Rekod kes mahkamah tidak dijumpai.');
        }

        $pdf = Pdf::loadView('pages.mahkamah.cetak-mahkamah-kes', [
            'kes' => $kes,
            'title' => 'Maklumat Kes Mahkamah',
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('kes-mahkamah-' . $id . '.pdf');
    }
}