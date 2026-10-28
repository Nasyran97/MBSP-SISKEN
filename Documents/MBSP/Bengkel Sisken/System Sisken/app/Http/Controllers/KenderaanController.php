<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class KenderaanController extends Controller
{
    private function checkCarListAccess()
    {
        $nostaff = session('nostaff');

        if (!$nostaff) {
            abort(403, 'Sila log masuk semula.');
        }

        $user = DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $nostaff)
            ->first();

        if (!$user) {
            abort(403, 'Akaun tidak dijumpai.');
        }

        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        if (!is_array($moduleRole)) {
            $moduleRole = [];
        }

        $allowedModules = collect($moduleRole)
            ->pluck('module')
            ->map(fn ($module) => strtoupper(trim($module)))
            ->toArray();

        if (!in_array('SUPERADMIN', $allowedModules) && !in_array('INSURANS', $allowedModules)) {
            abort(403, 'Anda tidak mempunyai akses ke modul ini.');
        }
    }

    private function getCarList()
    {
        return DB::connection('oracle')
            ->table('VEHICLE as v')
            ->leftJoin('PENGAWAS_VEHICLE as p', function ($join) {
                $join->on('p.VEHICLE_ID', '=', 'v.ID')
                    ->whereRaw("UPPER(p.STATUS_DATA) = 'ACTIVE'");
            })
            ->leftJoin('USERS as u', 'u.ID', '=', 'p.USER_ID')
            ->leftJoin('PEMBEKAL as pb', 'pb.ID', '=', 'v.PEMBEKAL_ID')
            ->select(
                DB::raw('v.ID as "ID"'),
                DB::raw('v.NO_PLAT as "NO_PLAT"'),
                DB::raw('v.JABATAN as "JABATAN"'),
                DB::raw('v.KOD_JABATAN as "KOD_JABATAN"'),
                DB::raw('v.NO_CHASIS as "NO_CHASIS"'),
                DB::raw('v.NO_ENJIN as "NO_ENJIN"'),
                DB::raw('v.BUATAN as "BUATAN"'),
                DB::raw('v.MODEL as "MODEL"'),
                DB::raw('v.JENIS_BADAN as "JENIS_BADAN"'),
                DB::raw('v.JENIS_KENDERAAN as "JENIS_KENDERAAN"'),
                DB::raw('v.TAHUN_DIBUAT as "TAHUN_DIBUAT"'),
                DB::raw('v.TARIKH_PENDAFTARAN as "TARIKH_PENDAFTARAN"'),
                DB::raw('v.KELAS_KENDERAAN as "KELAS_KENDERAAN"'),
                DB::raw('v.KEUPAYAAN_ENJIN as "KEUPAYAAN_ENJIN"'),
                DB::raw('v.KELAS_KEGUNAAN as "KELAS_KEGUNAAN"'),
                DB::raw('v.JENIS_BAHAN_BAKAR as "JENIS_BAHAN_BAKAR"'),
                DB::raw('v.LOKASI_PENEMPATAN as "LOKASI_PENEMPATAN"'),

                DB::raw('v.PEMBEKAL_ID as "PEMBEKAL_ID"'),
                DB::raw('pb.NAMA_PEMBEKAL as "NAMA_PEMBEKAL"'),
                DB::raw('pb.ALAMAT_PEMBEKAL as "ALAMAT_PEMBEKAL"'),

                DB::raw('v.TARIKH_BELI as "TARIKH_BELI"'),
                DB::raw('v.HARGA_BELI as "HARGA_BELI"'),

                DB::raw('v.GERAN_FILE as "GERAN_FILE"'),
                DB::raw('v.CN_FILE as "CN_FILE"'),
                DB::raw('v.STATUS_DATA as "STATUS_DATA"'),
                DB::raw('v.CREATED_AT as "CREATED_AT"'),
                DB::raw('v.CREATED_BY as "CREATED_BY"'),
                DB::raw('v.UPDATED_AT as "UPDATED_AT"'),
                DB::raw('v.UPDATED_BY as "UPDATED_BY"'),

                DB::raw('u.NAMA as "PENGAWAS_KENDERAAN"'),
                DB::raw('u.NOSTAFF as "PENGAWAS_NOSTAFF"')
            )
            ->whereRaw("UPPER(v.STATUS_DATA) IN ('ACTIVE', 'INACTIVE')")
            ->orderBy('v.ID', 'DESC')
            ->get();
    }

    public function carList()
    {
        $this->checkCarListAccess();

        $cars = $this->getCarList();

        return view('pages.kenderaan.rekod-car-list', [
            'title' => 'Senarai Kenderaan',
            'cars' => $cars,
        ]);
    }

    public function exportCarList()
    {
        $this->checkCarListAccess();

        $cars = $this->getCarList();

        $formatDate = function ($date) {
            if (!$date) {
                return '-';
            }

            try {
                return Carbon::parse($date)->format('d/m/Y');
            } catch (\Exception $e) {
                return '-';
            }
        };

        $headers = [
            'No Plat',
            'Buatan',
            'Model',
            'Jabatan',
            'Kod Jabatan',
            'Jenis Bahan Bakar',
            'Tahun Dibuat',
            'Jenis Badan',
            'Keupayaan Enjin',
            'No Chasis',
            'No Enjin',
            'Kelas Kegunaan',
            'Jenis Kenderaan',
            'Lokasi Penempatan',
            'Pengawas Kenderaan',
            'Tarikh Pendaftaran',
            'Kelas Kenderaan',
            'Nama Pembekal',
            'Alamat Pembekal',
            'Tarikh Beli',
            'Harga Beli',
        ];

        $filename = 'senarai-kenderaan-' . now()->format('Ymd_His') . '.csv';

        $callback = function () use ($cars, $headers, $formatDate) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($cars as $car) {
                fputcsv($handle, [
                    $car->NO_PLAT ?? $car->no_plat ?? '-',
                    $car->BUATAN ?? $car->buatan ?? '-',
                    $car->MODEL ?? $car->model ?? '-',
                    $car->JABATAN ?? $car->jabatan ?? '-',
                    $car->KOD_JABATAN ?? $car->kod_jabatan ?? '-',
                    $car->JENIS_BAHAN_BAKAR ?? $car->jenis_bahan_bakar ?? '-',
                    $car->TAHUN_DIBUAT ?? $car->tahun_dibuat ?? '-',
                    $car->JENIS_BADAN ?? $car->jenis_badan ?? '-',
                    $car->KEUPAYAAN_ENJIN ?? $car->keupayaan_enjin ?? '-',
                    $car->NO_CHASIS ?? $car->no_chasis ?? '-',
                    $car->NO_ENJIN ?? $car->no_enjin ?? '-',
                    $car->KELAS_KEGUNAAN ?? $car->kelas_kegunaan ?? '-',
                    $car->JENIS_KENDERAAN ?? $car->jenis_kenderaan ?? '-',
                    $car->LOKASI_PENEMPATAN ?? $car->lokasi_penempatan ?? '-',
                    $car->PENGAWAS_KENDERAAN ?? $car->pengawas_kenderaan ?? '-',
                    $formatDate($car->TARIKH_PENDAFTARAN ?? $car->tarikh_pendaftaran ?? null),
                    $car->KELAS_KENDERAAN ?? $car->kelas_kenderaan ?? '-',
                    $car->NAMA_PEMBEKAL ?? $car->nama_pembekal ?? '-',
                    $car->ALAMAT_PEMBEKAL ?? $car->alamat_pembekal ?? '-',
                    $formatDate($car->TARIKH_BELI ?? $car->tarikh_beli ?? null),
                    $car->HARGA_BELI ?? $car->harga_beli ?? '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function create()
    {
        $departments = [];

        try {
            $response = Http::timeout(10)->connectTimeout(5)->withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department', [
                    '6874be6b13269' => 'XHWBIEWPB0UKNLO505Q2B171'
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data'])) {
                    $departments = $data['data'];
                } else {
                    $departments = $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Department API error: ' . $e->getMessage());
        }

        $pengawas = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.KOD_JABATAN as "KOD_JABATAN"'),
                DB::raw('u.EMAIL as "EMAIL"'),
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
            ->whereRaw("(UPPER(u.STATUS_DATA) = 'ACTIVE' OR u.STATUS_DATA IS NULL)")
            ->orderBy('u.NAMA')
            ->get();

        $jenisBadan = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->select(
                DB::raw('JENIS_BADAN_NAME as "JENIS_BADAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('JENIS_BADAN_NAME')
            ->get();
        
        $kelasGuna = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->select(
                DB::raw('KELAS_GUNA_NAME as "KELAS_GUNA_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('KELAS_GUNA_NAME')
            ->get();

        $lokasiPenempatan = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->select(
                DB::raw('LOKASI_PENEMPATAN_NAME as "LOKASI_PENEMPATAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('LOKASI_PENEMPATAN_NAME')
            ->get();

        $pembekal = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAMA_PEMBEKAL as "NAMA_PEMBEKAL"'),
                DB::raw('ALAMAT_PEMBEKAL as "ALAMAT_PEMBEKAL"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('NAMA_PEMBEKAL')
            ->get();

        return view('pages.kenderaan.rekod-car-add', [
            'title' => 'Tambah Rekod Kenderaan',
            'departments' => $departments,
            'pengawas' => $pengawas,
            'jenisBadan' => $jenisBadan,
            'kelasGuna' => $kelasGuna,
            'lokasiPenempatan' => $lokasiPenempatan,
            'pembekal' => $pembekal,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kod_jabatan' => 'required',
            'jabatan' => 'required',
            'no_plat' => 'required',
            'buatan' => 'required',
            'model' => 'required',
            'jenis_bahan_bakar' => 'required',
            'tahun_dibuat' => 'required|digits:4',
            'jenis_badan' => 'required',
            'lokasi_penempatan' => 'required',
            'kelas_kenderaan' => 'required',
            'status_data' => 'required',

            'jenis_kenderaan' => 'nullable',
            'tarikh_pendaftaran' => 'nullable|date',
            'kelas_kegunaan' => 'nullable',

            'pembekal_id' => 'nullable',
            'tarikh_beli' => 'nullable|date',
            'harga_beli' => 'nullable',
        ]);

        $exists = DB::connection('oracle')
            ->table('VEHICLE')
            ->whereRaw('UPPER(NO_PLAT) = ?', [strtoupper(trim($request->no_plat))])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['no_plat' => 'No plat ini sudah wujud.'])
                ->withInput();
        }

        DB::connection('oracle')->table('VEHICLE')->insert([
            'NO_PLAT' => strtoupper(trim($request->no_plat)),
            'BUATAN' => strtoupper(trim($request->buatan)),
            'MODEL' => strtoupper(trim($request->model)),
            'KOD_JABATAN' => $request->kod_jabatan,
            'JABATAN' => $request->jabatan,
            'JENIS_BAHAN_BAKAR' => $request->jenis_bahan_bakar,
            'TAHUN_DIBUAT' => $request->tahun_dibuat,
            'JENIS_BADAN' => $request->jenis_badan,
            'KEUPAYAAN_ENJIN' => $request->keupayaan_enjin,
            'NO_CHASIS' => $request->no_chasis,
            'KELAS_KEGUNAAN' => $request->kelas_kegunaan,
            'NO_ENJIN' => $request->no_enjin,
            'LOKASI_PENEMPATAN' => $request->lokasi_penempatan,
            'JENIS_KENDERAAN' => $request->jenis_kenderaan,
            'TARIKH_PENDAFTARAN' => $request->tarikh_pendaftaran ? Carbon::parse($request->tarikh_pendaftaran) : null,
            'KELAS_KENDERAAN' => $request->kelas_kenderaan,
            'PEMBEKAL_ID' => $request->pembekal_id ?: null,
            'TARIKH_BELI' => $request->tarikh_beli ?: null,
            'HARGA_BELI' => $request->harga_beli
                ? (float) str_replace(',', '', $request->harga_beli)
                : null,
            'STATUS_DATA' => $request->status_data,
            'CREATED_AT' => now(),
            'CREATED_BY' => session('nostaff'),
        ]);

        return redirect()->route('rekod-car-list')
            ->with('success', 'Data kenderaan berjaya ditambah.');
    }

    public function edit($id)
    {
        $car = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$car) {
            abort(404);
        }

        $departments = [];

        try {
            $response = Http::timeout(10)->connectTimeout(5)->withBasicAuth('enforce', 'enforce@mbsp123')
                ->asForm()
                ->post('https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department', [
                    '6874be6b13269' => 'XHWBIEWPB0UKNLO505Q2B171'
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) {
                    $departments = $data['data'];
                } elseif (is_array($data)) {
                    $departments = $data;
                }
            }
        } catch (\Exception $e) {
            Log::error('Department API error: ' . $e->getMessage());
        }

        $pengawas = DB::connection('oracle')
            ->table('USERS as u')
            ->select(
                DB::raw('u.ID as "ID"'),
                DB::raw('u.NOSTAFF as "NOSTAFF"'),
                DB::raw('u.NAMA as "NAMA"'),
                DB::raw('u.JABATAN as "JABATAN"'),
                DB::raw('u.KOD_JABATAN as "KOD_JABATAN"'),
                DB::raw('u.EMAIL as "EMAIL"'),
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
            ->whereRaw("(UPPER(u.STATUS_DATA) = 'ACTIVE' OR u.STATUS_DATA IS NULL)")
            ->orderBy('u.NAMA')
            ->get();

        $selectedPengawasIds = DB::connection('oracle')
            ->table('PENGAWAS_VEHICLE')
            ->where('VEHICLE_ID', $id)
            ->whereRaw("UPPER(STATUS_DATA) = 'ACTIVE'")
            ->pluck('USER_ID')
            ->map(function ($value) {
                return (string) $value;
            })
            ->toArray();

        $jenisBadan = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->select(
                DB::raw('JENIS_BADAN_NAME as "JENIS_BADAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('JENIS_BADAN_NAME')
            ->get();

        $kelasGuna = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->select(
                DB::raw('KELAS_GUNA_NAME as "KELAS_GUNA_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('KELAS_GUNA_NAME')
            ->get();

        $lokasiPenempatan = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->select(
                DB::raw('LOKASI_PENEMPATAN_NAME as "LOKASI_PENEMPATAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('LOKASI_PENEMPATAN_NAME')
            ->get();

        $pembekal = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAMA_PEMBEKAL as "NAMA_PEMBEKAL"'),
                DB::raw('ALAMAT_PEMBEKAL as "ALAMAT_PEMBEKAL"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('NAMA_PEMBEKAL')
            ->get();

        return view('pages.kenderaan.rekod-car-edit', [
            'title' => 'Edit Rekod Kenderaan',
            'car' => $car,
            'departments' => $departments,
            'pengawas' => $pengawas,
            'selectedPengawasIds' => $selectedPengawasIds,
            'jenisBadan' => $jenisBadan,
            'kelasGuna' => $kelasGuna,
            'lokasiPenempatan' => $lokasiPenempatan,
            'pembekal' => $pembekal,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            // 'kod_jabatan' => 'required',
            // 'jabatan' => 'required',
            'no_plat' => 'required',
            'buatan' => 'required',
            // 'model' => 'required',
            'jenis_bahan_bakar' => 'required',
            'tahun_dibuat' => 'required',
            'jenis_badan' => 'required',
            // 'lokasi_penempatan' => 'required',
            'kelas_kenderaan' => 'required',
            'status_data' => 'required',
            'tarikh_pendaftaran' => 'nullable|date',
            'pembekal_id' => 'nullable',
            'tarikh_beli' => 'nullable|date',
            'harga_beli' => 'nullable',
        ]);

        $exists = DB::connection('oracle')
            ->table('VEHICLE')
            ->whereRaw('UPPER(NO_PLAT) = ?', [strtoupper(trim($request->no_plat))])
            ->where('ID', '!=', $id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['no_plat' => 'No plat ini sudah wujud.'])
                ->withInput();
        }

        DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->update([
                'NO_PLAT' => strtoupper(trim($request->no_plat)),
                'BUATAN' => strtoupper(trim($request->buatan)),
                'MODEL' => strtoupper(trim($request->model)),
                'KOD_JABATAN' => $request->kod_jabatan,
                'JABATAN' => $request->jabatan,
                'JENIS_BAHAN_BAKAR' => $request->jenis_bahan_bakar,
                'TAHUN_DIBUAT' => $request->tahun_dibuat,
                'JENIS_BADAN' => $request->jenis_badan,
                'KEUPAYAAN_ENJIN' => $request->keupayaan_enjin,
                'NO_CHASIS' => $request->no_chasis,
                'KELAS_KEGUNAAN' => $request->kelas_kegunaan,
                'NO_ENJIN' => $request->no_enjin,
                'LOKASI_PENEMPATAN' => $request->lokasi_penempatan,
                'JENIS_KENDERAAN' => $request->jenis_kenderaan,
                'TARIKH_PENDAFTARAN' => $request->tarikh_pendaftaran,
                'KELAS_KENDERAAN' => $request->kelas_kenderaan,
                'PEMBEKAL_ID' => $request->pembekal_id ?: null,
                'TARIKH_BELI' => $request->tarikh_beli ?: null,
                'HARGA_BELI' => $request->harga_beli
                    ? (float) str_replace(',', '', $request->harga_beli)
                    : null,
                'STATUS_DATA' => $request->status_data,
                'UPDATED_AT' => now(),
                'UPDATED_BY' => session('nostaff'),
            ]);

        return redirect()->route('rekod-car-list')
            ->with('success', 'Rekod kenderaan berjaya dikemaskini.');
    }

        public function uploadVehicleFile(Request $request, $id)
    {
        $request->validate([
            'file_type' => 'required|in:geran,cn',
            'upload_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $fileType = $request->file_type;
        $column = $fileType === 'geran' ? 'GERAN_FILE' : 'CN_FILE';

        $vehicle = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$vehicle) {
            return back()->with('error', 'Rekod kenderaan tidak dijumpai.');
        }

        $oldFile = $vehicle->$column ?? null;

        if ($oldFile && Storage::disk('synology')->exists($oldFile)) {
            Storage::disk('synology')->delete($oldFile);
        }

        $file = $request->file('upload_file');

        $folder = 'Geran dan CN/' . now()->format('Y') . '/' . now()->format('m');

        $originalName = $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();

        $newFileName = now()->format('Ymd_His') . '_' . $safeName . '.' . $extension;
        $filePath = $folder . '/' . $newFileName;

        // Pass the raw temp path: on IIS, getRealPath() returns false when the app pool
        // cannot traverse the temp dir, which makes putFileAs() fail with "Path cannot be empty".
        Storage::disk('synology')->putFileAs($folder, $file->getPathname(), $newFileName);

        DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->update([
                $column => $filePath,
                'UPDATED_AT' => now(),
                'UPDATED_BY' => session('nostaff'),
            ]);

        return back()->with('success', strtoupper($fileType) . ' berjaya dikemaskini.');
    }

    public function deleteVehicleFile(Request $request, $id)
    {
        $request->validate([
            'file_type' => 'required|in:geran,cn',
        ]);

        $fileType = $request->file_type;
        $column = $fileType === 'geran' ? 'GERAN_FILE' : 'CN_FILE';

        $vehicle = DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->first();

        if (!$vehicle) {
            return back()->with('error', 'Rekod kenderaan tidak dijumpai.');
        }

        $filePath = $vehicle->$column ?? null;

        if ($filePath && Storage::disk('synology')->exists($filePath)) {
            Storage::disk('synology')->delete($filePath);
        }

        DB::connection('oracle')
            ->table('VEHICLE')
            ->where('ID', $id)
            ->update([
                $column => null,
                'UPDATED_AT' => now(),
                'UPDATED_BY' => session('nostaff'),
            ]);

        return back()->with('success', strtoupper($fileType) . ' berjaya dipadam.');
    }

    public function updateDepartment(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | External API configuration
        |--------------------------------------------------------------------------
        | Replace the token and password below with the values from your
        | API documentation.
        */
    
        $apiUrl = 'https://apimbsp.mbsp.gov.my/public/APIS_6874be6b13258/data/department';
        $postKey = '6874be6b13269';
        $token = 'XHWBIEWPB0UKNLO505Q2B171';
    
        $username = 'enforce';
        $password = 'enforce@mbsp123';
    
        /*
        |--------------------------------------------------------------------------
        | Optional department code
        |--------------------------------------------------------------------------
        | Example:
        | /kenderaan/update-department?depCode=101
        |
        | Leave empty to update all department codes in VEHICLE.
        */
    
        $requestedCode = trim((string) $request->query('depCode', ''));
    
        try {
            $response = Http::withBasicAuth($username, $password)
                ->asForm()
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(60)
                ->retry(3, 1000)
                ->post($apiUrl, [
                    $postKey => $token,
                ]);
    
            if ($response->failed()) {
                Log::error('Department API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
    
                return response()->json([
                    'success' => false,
                    'message' => 'External department API request failed.',
                    'status' => $response->status(),
                    'response' => $response->body(),
                ], 502);
            }
    
            $payload = $response->json();
    
            /*
            |--------------------------------------------------------------------------
            | Normalize API response
            |--------------------------------------------------------------------------
            | Supports:
            |
            | Single response:
            | {
            |     "CODE": "101",
            |     "DESCRIPTION": "JABATAN KHIDMAT PENGURUSAN"
            | }
            |
            | Multiple response:
            | [
            |     {
            |         "CODE": "101",
            |         "DESCRIPTION": "JABATAN KHIDMAT PENGURUSAN"
            |     }
            | ]
            |
            | Wrapped response:
            | {
            |     "data": [...]
            | }
            */
    
            if (!is_array($payload)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response received from department API.',
                ], 422);
            }
    
            if (isset($payload['data']) && is_array($payload['data'])) {
                $payload = $payload['data'];
            } elseif (isset($payload['DATA']) && is_array($payload['DATA'])) {
                $payload = $payload['DATA'];
            } elseif (
                array_key_exists('CODE', $payload) ||
                array_key_exists('code', $payload)
            ) {
                $payload = [$payload];
            }
    
            $departmentMap = [];
    
            foreach ($payload as $department) {
                if (!is_array($department)) {
                    continue;
                }
    
                $code = trim((string) (
                    $department['CODE']
                    ?? $department['code']
                    ?? ''
                ));
    
                $description = trim((string) (
                    $department['DESCRIPTION']
                    ?? $department['description']
                    ?? ''
                ));
    
                if ($code === '' || $description === '') {
                    continue;
                }
    
                $departmentMap[$code] = $description;
            }
    
            if (empty($departmentMap)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid CODE and DESCRIPTION found in API response.',
                    'api_response' => $payload,
                ], 422);
            }
    
            /*
            |--------------------------------------------------------------------------
            | Get department codes from VEHICLE
            |--------------------------------------------------------------------------
            */
    
            $vehicleCodesQuery = DB::connection('oracle')->table('VEHICLE')
                ->whereNotNull('kod_jabatan')
                ->selectRaw('TRIM(kod_jabatan) AS kod_jabatan')
                ->distinct();
    
            if ($requestedCode !== '') {
                $vehicleCodesQuery->whereRaw(
                    'TRIM(kod_jabatan) = ?',
                    [$requestedCode]
                );
            }
    
            $vehicleCodes = $vehicleCodesQuery
                ->pluck('kod_jabatan')
                ->map(fn ($code) => trim((string) $code))
                ->filter()
                ->unique()
                ->values();
    
            $totalUpdatedRows = 0;
            $updatedDepartments = [];
            $notFoundCodes = [];
    
            DB::transaction(function () use (
                $vehicleCodes,
                $departmentMap,
                &$totalUpdatedRows,
                &$updatedDepartments,
                &$notFoundCodes
            ) {
                foreach ($vehicleCodes as $code) {
                    if (!isset($departmentMap[$code])) {
                        $notFoundCodes[] = $code;
                        continue;
                    }
    
                    $departmentName = $departmentMap[$code];
    
                    $updatedRows = DB::connection('oracle')->table('VEHICLE')
                        ->whereRaw('TRIM(kod_jabatan) = ?', [$code])
                        ->update([
                            'JABATAN' => $departmentName,
                        ]);
    
                    $totalUpdatedRows += $updatedRows;
    
                    $updatedDepartments[] = [
                        'kod_jabatan' => $code,
                        'jabatan' => $departmentName,
                        'updated_rows' => $updatedRows,
                    ];
                }
            });
    
            return response()->json([
                'success' => true,
                'message' => 'Department names updated successfully.',
                'api_department_count' => count($departmentMap),
                'vehicle_department_code_count' => $vehicleCodes->count(),
                'updated_rows' => $totalUpdatedRows,
                'updated_departments' => $updatedDepartments,
                'codes_not_found_in_api' => $notFoundCodes,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to update vehicle department names', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department names.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function jenisBadanList()
    {
        $jenisBadan = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('JENIS_BADAN_NAME as "JENIS_BADAN_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('JENIS_BADAN_NAME')
            ->get();
    
        return view('pages.kenderaan.jenis-badan-list', [
            'title' => 'Senarai Jenis Badan',
            'jenisBadan' => $jenisBadan,
        ]);
    }
    
    public function jenisBadanAdd()
    {
        return view('pages.kenderaan.jenis-badan-add', [
            'title' => 'Tambah Jenis Badan',
        ]);
    }
    
    public function jenisBadanStore(Request $request)
    {
        $request->validate([
            'jenis_badan_name' => 'required|string|max:255',
        ], [
            'jenis_badan_name.required' => 'Sila masukkan jenis badan.',
        ]);
    
        $jenisBadanName = strtoupper(trim($request->jenis_badan_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->whereRaw('UPPER(JENIS_BADAN_NAME) = ?', [$jenisBadanName])
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis badan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->insert([
                'JENIS_BADAN_NAME' => $jenisBadanName,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);
    
        return redirect()
            ->route('jenis-badan-list')
            ->with('success', 'Jenis badan berjaya ditambah.');
    }
    
    public function jenisBadanUpdate(Request $request, $id)
    {
        $request->validate([
            'jenis_badan_name' => 'required|string|max:255',
        ], [
            'jenis_badan_name.required' => 'Sila masukkan jenis badan.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod jenis badan tidak dijumpai.');
        }
    
        $jenisBadanName = strtoupper(trim($request->jenis_badan_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->whereRaw('UPPER(JENIS_BADAN_NAME) = ?', [$jenisBadanName])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis badan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->where('ID', $id)
            ->update([
                'JENIS_BADAN_NAME' => $jenisBadanName,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Jenis badan berjaya dikemaskini.');
    }
    
    public function jenisBadanDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod jenis badan tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_JENIS_BADAN')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Jenis badan berjaya dipadam.');
    }

    public function kelasGunaList()
    {
        $kelasGuna = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('KELAS_GUNA_NAME as "KELAS_GUNA_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('KELAS_GUNA_NAME')
            ->get();
    
        return view('pages.kenderaan.kelas-guna-list', [
            'title' => 'Senarai Kelas Kegunaan',
            'kelasGuna' => $kelasGuna,
        ]);
    }
    
    public function kelasGunaAdd()
    {
        return view('pages.kenderaan.kelas-guna-add', [
            'title' => 'Tambah Kelas Kegunaan',
        ]);
    }
    
    public function kelasGunaStore(Request $request)
    {
        $request->validate([
            'kelas_guna_name' => 'required|string|max:255',
        ], [
            'kelas_guna_name.required' => 'Sila masukkan kelas kegunaan.',
        ]);
    
        $kelasGunaName = strtoupper(trim($request->kelas_guna_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->whereRaw('UPPER(KELAS_GUNA_NAME) = ?', [$kelasGunaName])
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Kelas kegunaan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->insert([
                'KELAS_GUNA_NAME' => $kelasGunaName,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);
    
        return redirect()
            ->route('kelas-guna-list')
            ->with('success', 'Kelas kegunaan berjaya ditambah.');
    }
    
    public function kelasGunaUpdate(Request $request, $id)
    {
        $request->validate([
            'kelas_guna_name' => 'required|string|max:255',
        ], [
            'kelas_guna_name.required' => 'Sila masukkan kelas kegunaan.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod kelas kegunaan tidak dijumpai.');
        }
    
        $kelasGunaName = strtoupper(trim($request->kelas_guna_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->whereRaw('UPPER(KELAS_GUNA_NAME) = ?', [$kelasGunaName])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Kelas kegunaan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->where('ID', $id)
            ->update([
                'KELAS_GUNA_NAME' => $kelasGunaName,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Kelas kegunaan berjaya dikemaskini.');
    }
    
    public function kelasGunaDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod kelas kegunaan tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_KELAS_GUNA')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Kelas kegunaan berjaya dipadam.');
    }

    public function lokasiPenempatanList()
    {
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
    
        return view('pages.kenderaan.lokasi-penempatan-list', [
            'title' => 'Senarai Lokasi Penempatan',
            'lokasiPenempatan' => $lokasiPenempatan,
        ]);
    }
    
    public function lokasiPenempatanAdd()
    {
        return view('pages.kenderaan.lokasi-penempatan-add', [
            'title' => 'Tambah Lokasi Penempatan',
        ]);
    }
    
    public function lokasiPenempatanStore(Request $request)
    {
        $request->validate([
            'lokasi_penempatan_name' => 'required|string|max:255',
        ], [
            'lokasi_penempatan_name.required' => 'Sila masukkan lokasi penempatan.',
        ]);
    
        $lokasiPenempatanName = strtoupper(trim($request->lokasi_penempatan_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->whereRaw('UPPER(TRIM(LOKASI_PENEMPATAN_NAME)) = ?', [$lokasiPenempatanName])
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Lokasi penempatan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->insert([
                'LOKASI_PENEMPATAN_NAME' => $lokasiPenempatanName,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);
    
        return redirect()
            ->route('lokasi-penempatan-list')
            ->with('success', 'Lokasi penempatan berjaya ditambah.');
    }
    
    public function lokasiPenempatanUpdate(Request $request, $id)
    {
        $request->validate([
            'lokasi_penempatan_name' => 'required|string|max:255',
        ], [
            'lokasi_penempatan_name.required' => 'Sila masukkan lokasi penempatan.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod lokasi penempatan tidak dijumpai.');
        }
    
        $lokasiPenempatanName = strtoupper(trim($request->lokasi_penempatan_name));
    
        $exists = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->whereRaw('UPPER(TRIM(LOKASI_PENEMPATAN_NAME)) = ?', [$lokasiPenempatanName])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Lokasi penempatan ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->where('ID', $id)
            ->update([
                'LOKASI_PENEMPATAN_NAME' => $lokasiPenempatanName,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Lokasi penempatan berjaya dikemaskini.');
    }
    
    public function lokasiPenempatanDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod lokasi penempatan tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Lokasi penempatan berjaya dipadam.');
    }

    public function pembekalList()
    {
        $pembekal = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('NAMA_PEMBEKAL as "NAMA_PEMBEKAL"'),
                DB::raw('ALAMAT_PEMBEKAL as "ALAMAT_PEMBEKAL"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('NAMA_PEMBEKAL')
            ->get();

        return view('pages.kenderaan.pembekal-list', [
            'title' => 'Senarai Pembekal',
            'pembekal' => $pembekal,
        ]);
    }

    public function pembekalAdd()
    {
        return view('pages.kenderaan.pembekal-add', [
            'title' => 'Tambah Pembekal',
        ]);
    }

    public function pembekalStore(Request $request)
    {
        $request->validate([
            'nama_pembekal' => 'required|string|max:255',
            'alamat_pembekal' => 'nullable|string|max:1000',
        ], [
            'nama_pembekal.required' => 'Sila masukkan nama pembekal.',
        ]);

        $namaPembekal = strtoupper(trim($request->nama_pembekal));
        $alamatPembekal = $request->alamat_pembekal
            ? strtoupper(trim(preg_replace('/\s+/', ' ', $request->alamat_pembekal)))
            : null;

        $exists = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->whereRaw('UPPER(TRIM(NAMA_PEMBEKAL)) = ?', [$namaPembekal])
            ->whereRaw("NVL(UPPER(TRIM(ALAMAT_PEMBEKAL)), '-') = ?", [$alamatPembekal ?: '-'])
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pembekal ini telah wujud.');
        }

        DB::connection('oracle')
            ->table('PEMBEKAL')
            ->insert([
                'NAMA_PEMBEKAL' => $namaPembekal,
                'ALAMAT_PEMBEKAL' => $alamatPembekal,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now(),
                'STATUS_DATA' => 'ACTIVE',
            ]);

        return redirect()
            ->route('pembekal-list')
            ->with('success', 'Pembekal berjaya ditambah.');
    }

    public function pembekalUpdate(Request $request, $id)
    {
        $request->validate([
            'nama_pembekal' => 'required|string|max:255',
            'alamat_pembekal' => 'nullable|string|max:1000',
        ], [
            'nama_pembekal.required' => 'Sila masukkan nama pembekal.',
        ]);

        $record = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod pembekal tidak dijumpai.');
        }

        $namaPembekal = strtoupper(trim($request->nama_pembekal));
        $alamatPembekal = $request->alamat_pembekal
            ? strtoupper(trim(preg_replace('/\s+/', ' ', $request->alamat_pembekal)))
            : null;

        $exists = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->whereRaw('UPPER(TRIM(NAMA_PEMBEKAL)) = ?', [$namaPembekal])
            ->whereRaw("NVL(UPPER(TRIM(ALAMAT_PEMBEKAL)), '-') = ?", [$alamatPembekal ?: '-'])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pembekal ini telah wujud.');
        }

        DB::connection('oracle')
            ->table('PEMBEKAL')
            ->where('ID', $id)
            ->update([
                'NAMA_PEMBEKAL' => $namaPembekal,
                'ALAMAT_PEMBEKAL' => $alamatPembekal,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now(),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Pembekal berjaya dikemaskini.');
    }

    public function pembekalDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('PEMBEKAL')
            ->where('ID', $id)
            ->first();

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod pembekal tidak dijumpai.');
        }

        DB::connection('oracle')
            ->table('PEMBEKAL')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now(),
            ]);

        return redirect()
            ->back()
            ->with('success', 'Pembekal berjaya dipadam.');
    }
}





?>