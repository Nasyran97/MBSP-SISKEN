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

class TuntutanController extends Controller
{
    public function tuntutanAwamList()
    {
        $tuntutanAwam = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            // ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_NAME')
            ->get();

        return view('pages.tuntutan.tuntutan-awam-list', [
            'title' => 'Senarai Jenis Tuntutan Awam',
            'tuntutanAwam' => $tuntutanAwam,
        ]);
    }

    public function tuntutanAwamAdd()
    {
        return view('pages.tuntutan.tuntutan-awam-add', [
            'title' => 'Tambah Jenis Tuntutan Awam',
        ]);
    }

    public function tuntutanAwamStore(Request $request)
    {
        $request->validate([
            'tuntutan_name' => 'required|string|max:255',
        ], [
            'tuntutan_name.required' => 'Sila masukkan nama jenis tuntutan awam.',
        ]);
    
        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->whereRaw('UPPER(TUNTUTAN_NAME) = ?', [strtoupper($request->tuntutan_name)])
            ->first();

        if ($exists) {
            $status = strtoupper($exists->STATUS_DATA ?? $exists->status_data ?? '');

            if ($status === 'DELETE') {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Jenis tuntutan awam ini telah wujud tetapi berstatus DELETE. Sila aktifkan semula di senarai.');
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan awam ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->insert([
                'TUNTUTAN_NAME' => strtoupper(trim($request->tuntutan_name)),
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);
    
        return redirect()
            ->route('tuntutan-awam-list')
            ->with('success', 'Jenis tuntutan awam berjaya ditambah.');
    }

    public function tuntutanAwamUpdate(Request $request, $id)
    {
        $request->validate([
            'tuntutan_name' => 'required|string|max:255',
        ], [
            'tuntutan_name.required' => 'Sila masukkan nama jenis tuntutan awam.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }
    
        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->whereRaw('UPPER(TUNTUTAN_NAME) = ?', [strtoupper($request->tuntutan_name)])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->exists();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan awam ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->update([
                'TUNTUTAN_NAME' => strtoupper(trim($request->tuntutan_name)),
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan awam berjaya dikemaskini.');
    }

    public function tuntutanAwamDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan awam berjaya dipadam.');
    }

    public function tuntutanAwamActivate($id)
    {
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'ACTIVE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Jenis tuntutan awam berjaya diaktifkan semula.');
    }

    public function liabilitiAwamAdd()
    {
        $jenisTuntutanAwam = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_NAME')
            ->get();
        
        $lokasiPenempatan = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('LOKASI_PENEMPATAN_NAME as "LOKASI_PENEMPATAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('LOKASI_PENEMPATAN_NAME')
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
    
        return view('pages.liabiliti.liabiliti-data-add', [
            'title' => 'Tambah Tuntutan Awam',
            'jenisTuntutanAwam' => $jenisTuntutanAwam,
            'departments' => $departments,
            'lokasiPenempatan' => $lokasiPenempatan,
        ]);
    }

    public function liabilitiAwamStore(Request $request)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'no_plat_harta_benda' => 'required|string|max:255',
            'tarikh_hantar_kes' => 'required|date',
            'tuntutan_awam_id' => 'required',
            'nama_penuntut' => 'required|string|max:255',
            'tarikh_kejadian' => 'required|date',
            'amaun_tuntutan' => 'required|numeric|min:0',
            'no_telefon' => 'nullable|string|max:50',
            'tarikh_offer_letter' => 'nullable|date',
            'lokasi_kejadian' => 'required|string|max:1000',
            'jabatan_kod' => 'required|string|max:50',
            'jabatan_nama' => 'nullable|string|max:255',
            'bahagian' => 'required|string|max:100',
            'daerah_kejadian' => 'required|string|max:50',
            'status_tuntutan' => 'required|string|in:DALAM PROSES,TINDAKAN ADJUSTER,SELESAI TANDATANGAN OFFER LETTER',
            'keputusan_kes' => 'nullable|string|in:LULUS,DITOLAK',
            'laporan_adjuster_insurans' => 'nullable|string',

            'lampiran_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tahun.required' => 'Sila masukkan tahun.',
            'tahun.digits' => 'Tahun mestilah 4 digit.',
            'no_plat_harta_benda.required' => 'Sila masukkan no plat / harta benda.',
            'tarikh_hantar_kes.required' => 'Sila pilih tarikh hantar kes.',
            'tuntutan_awam_id.required' => 'Sila pilih jenis tuntutan.',
            'nama_penuntut.required' => 'Sila masukkan nama penuntut.',
            'tarikh_kejadian.required' => 'Sila pilih tarikh kejadian.',
            'amaun_tuntutan.required' => 'Sila masukkan amaun tuntutan.',
            'lokasi_kejadian.required' => 'Sila masukkan lokasi kejadian / alamat rumah.',
            'jabatan_kod.required' => 'Sila pilih jabatan.',
            'bahagian.required' => 'Sila pilih bahagian.',
            'daerah_kejadian.required' => 'Sila pilih daerah kejadian.',
            'status_tuntutan.required' => 'Sila pilih status tuntutan.',

            'lampiran_file.mimes' => 'Fail mestilah dalam format PDF, JPG, JPEG atau PNG.',
            'lampiran_file.max' => 'Saiz fail maksimum ialah 5MB.',
        ]);

        $jenisTuntutan = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $request->tuntutan_awam_id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if (!$jenisTuntutan) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan awam tidak dijumpai atau tidak aktif.');
        }

        $tuntutanName = $jenisTuntutan->TUNTUTAN_NAME
            ?? $jenisTuntutan->tuntutan_name
            ?? null;

        $lampiranFilePath = null;

        if ($request->hasFile('lampiran_file')) {
            $file = $request->file('lampiran_file');

            $folder = 'Liabiliti Awam/' . now('Asia/Kuala_Lumpur')->format('Y') . '/' . now('Asia/Kuala_Lumpur')->format('m');

            $platNo = strtoupper(trim($request->no_plat_harta_benda));
            $safePlatNo = Str::slug($platNo);

            if (!$safePlatNo) {
                $safePlatNo = 'harta-benda';
            }

            $extension = strtolower($file->getClientOriginalExtension());

            $newFileName = now('Asia/Kuala_Lumpur')->format('Ymd_His') . '_' . $safePlatNo . '.' . $extension;

            $lampiranFilePath = $folder . '/' . $newFileName;

            Storage::disk('synology')->putFileAs($folder, $file, $newFileName);
        }

        DB::connection('oracle')
            ->table('LIABILITI_AWAM')
            ->insert([
                'TAHUN' => $request->tahun,
                'NO_PLAT_HARTA_BENDA' => strtoupper(trim($request->no_plat_harta_benda)),
                'TARIKH_HANTAR_KES' => $request->tarikh_hantar_kes,

                'TUNTUTAN_AWAM_ID' => $request->tuntutan_awam_id,
                'JENIS_TUNTUTAN' => $tuntutanName,

                'NAMA_PENUNTUT' => strtoupper(trim($request->nama_penuntut)),
                'TARIKH_KEJADIAN' => $request->tarikh_kejadian,
                'AMAUN_TUNTUTAN' => $request->amaun_tuntutan,
                'NO_TELEFON' => $request->no_telefon,
                'TARIKH_OFFER_LETTER' => $request->tarikh_offer_letter,

                'LOKASI_KEJADIAN' => $request->lokasi_kejadian,
                'JABATAN_KOD' => $request->jabatan_kod,
                'JABATAN_NAMA' => $request->jabatan_nama,

                'BAHAGIAN' => $request->bahagian,
                'DAERAH_KEJADIAN' => $request->daerah_kejadian,

                'STATUS_TUNTUTAN' => $request->status_tuntutan,
                'KEPUTUSAN_KES' => $request->keputusan_kes,

                'LAPORAN_ADJUSTER_INSURANS' => $request->laporan_adjuster_insurans,

                'LAMPIRAN_FILE' => $lampiranFilePath,

                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);

        return redirect()
            ->route('liabiliti-data-add')
            ->with('success', 'Tuntutan awam berjaya disimpan.');
    }

    public function liabilitiKesList()
    {
        $liabiliti = DB::connection('oracle')
            ->table('LIABILITI_AWAM as l')
            ->leftJoin('VEHICLE_LOKASI_PENEMPATAN as vlp', function ($join) {
                $join->on(DB::raw('TO_CHAR(vlp.ID)'), '=', DB::raw('TRIM(l.BAHAGIAN)'))
                    ->whereRaw("(UPPER(vlp.STATUS_DATA) = 'ACTIVE' OR vlp.STATUS_DATA IS NULL)");
            })
            ->select(
                DB::raw('l.ID as "ID"'),
                DB::raw('l.TAHUN as "TAHUN"'),
                DB::raw('l.NO_PLAT_HARTA_BENDA as "NO_PLAT_HARTA_BENDA"'),
                DB::raw('l.TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('l.TUNTUTAN_AWAM_ID as "TUNTUTAN_AWAM_ID"'),
                DB::raw('l.JENIS_TUNTUTAN as "JENIS_TUNTUTAN"'),
                DB::raw('l.NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('l.TARIKH_KEJADIAN as "TARIKH_KEJADIAN"'),
                DB::raw('l.AMAUN_TUNTUTAN as "AMAUN_TUNTUTAN"'),
                DB::raw('l.NO_TELEFON as "NO_TELEFON"'),
                DB::raw('l.TARIKH_OFFER_LETTER as "TARIKH_OFFER_LETTER"'),
                DB::raw('l.LOKASI_KEJADIAN as "LOKASI_KEJADIAN"'),
                DB::raw('l.JABATAN_KOD as "JABATAN_KOD"'),
                DB::raw('l.JABATAN_NAMA as "JABATAN_NAMA"'),

                DB::raw('l.BAHAGIAN as "BAHAGIAN"'),
                DB::raw('vlp.LOKASI_PENEMPATAN_NAME as "BAHAGIAN_NAME"'),

                DB::raw('l.DAERAH_KEJADIAN as "DAERAH_KEJADIAN"'),
                DB::raw('l.STATUS_TUNTUTAN as "STATUS_TUNTUTAN"'),
                DB::raw('l.KEPUTUSAN_KES as "KEPUTUSAN_KES"'),
                DB::raw('l.LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),
                DB::raw('l.LAMPIRAN_FILE as "LAMPIRAN_FILE"'),
                DB::raw('l.CREATED_BY as "CREATED_BY"'),
                DB::raw('l.CREATED_AT as "CREATED_AT"'),
                DB::raw('l.UPDATED_BY as "UPDATED_BY"'),
                DB::raw('l.UPDATED_AT as "UPDATED_AT"'),
                DB::raw('l.STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(l.STATUS_DATA) = 'ACTIVE' OR l.STATUS_DATA IS NULL)")
            ->orderBy('l.TARIKH_HANTAR_KES', 'desc')
            ->orderBy('l.ID', 'desc')
            ->get();

        return view('pages.liabiliti.liabiliti-kes-list', [
            'title' => 'Senarai Kes Liabiliti Awam',
            'liabiliti' => $liabiliti,
        ]);
    }

    public function printLiabilitiKes($id)
    {
        $kes = DB::connection('oracle')
            ->table('LIABILITI_AWAM')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TAHUN as "TAHUN"'),
                DB::raw('NO_PLAT_HARTA_BENDA as "NO_PLAT_HARTA_BENDA"'),
                DB::raw('TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('TUNTUTAN_AWAM_ID as "TUNTUTAN_AWAM_ID"'),
                DB::raw('JENIS_TUNTUTAN as "JENIS_TUNTUTAN"'),
                DB::raw('NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('TARIKH_KEJADIAN as "TARIKH_KEJADIAN"'),
                DB::raw('AMAUN_TUNTUTAN as "AMAUN_TUNTUTAN"'),
                DB::raw('NO_TELEFON as "NO_TELEFON"'),
                DB::raw('TARIKH_OFFER_LETTER as "TARIKH_OFFER_LETTER"'),
                DB::raw('LOKASI_KEJADIAN as "LOKASI_KEJADIAN"'),
                DB::raw('JABATAN_KOD as "JABATAN_KOD"'),
                DB::raw('JABATAN_NAMA as "JABATAN_NAMA"'),
                DB::raw('BAHAGIAN as "BAHAGIAN"'),
                DB::raw('DAERAH_KEJADIAN as "DAERAH_KEJADIAN"'),
                DB::raw('STATUS_TUNTUTAN as "STATUS_TUNTUTAN"'),
                DB::raw('KEPUTUSAN_KES as "KEPUTUSAN_KES"'),
                DB::raw('LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->where('ID', $id)
            ->first();
    
        if (!$kes) {
            abort(404, 'Rekod tidak dijumpai.');
        }
    
        $pdf = Pdf::loadView('pages.liabiliti.liabiliti-kes-print', [
            'kes' => $kes,
        ])->setPaper('a4', 'portrait');
    
        return $pdf->stream('liabiliti-awam-' . $id . '.pdf');
    }

    public function liabilitiKesEdit($id)
    {
        $kes = DB::connection('oracle')
            ->table('LIABILITI_AWAM as l')
            ->leftJoin('VEHICLE_LOKASI_PENEMPATAN as vlp', function ($join) {
                $join->on(DB::raw('TO_CHAR(vlp.ID)'), '=', DB::raw('TRIM(l.BAHAGIAN)'))
                    ->whereRaw("(UPPER(vlp.STATUS_DATA) = 'ACTIVE' OR vlp.STATUS_DATA IS NULL)");
            })
            ->select(
                DB::raw('l.ID as "ID"'),
                DB::raw('l.TAHUN as "TAHUN"'),
                DB::raw('l.NO_PLAT_HARTA_BENDA as "NO_PLAT_HARTA_BENDA"'),
                DB::raw('l.TARIKH_HANTAR_KES as "TARIKH_HANTAR_KES"'),
                DB::raw('l.TUNTUTAN_AWAM_ID as "TUNTUTAN_AWAM_ID"'),
                DB::raw('l.JENIS_TUNTUTAN as "JENIS_TUNTUTAN"'),
                DB::raw('l.NAMA_PENUNTUT as "NAMA_PENUNTUT"'),
                DB::raw('l.TARIKH_KEJADIAN as "TARIKH_KEJADIAN"'),
                DB::raw('l.AMAUN_TUNTUTAN as "AMAUN_TUNTUTAN"'),
                DB::raw('l.NO_TELEFON as "NO_TELEFON"'),
                DB::raw('l.TARIKH_OFFER_LETTER as "TARIKH_OFFER_LETTER"'),
                DB::raw('l.LOKASI_KEJADIAN as "LOKASI_KEJADIAN"'),
                DB::raw('l.JABATAN_KOD as "JABATAN_KOD"'),
                DB::raw('l.JABATAN_NAMA as "JABATAN_NAMA"'),

                DB::raw('l.BAHAGIAN as "BAHAGIAN"'),
                DB::raw('vlp.LOKASI_PENEMPATAN_NAME as "BAHAGIAN_NAME"'),

                DB::raw('l.DAERAH_KEJADIAN as "DAERAH_KEJADIAN"'),
                DB::raw('l.STATUS_TUNTUTAN as "STATUS_TUNTUTAN"'),
                DB::raw('l.KEPUTUSAN_KES as "KEPUTUSAN_KES"'),
                DB::raw('l.LAPORAN_ADJUSTER_INSURANS as "LAPORAN_ADJUSTER_INSURANS"'),
                DB::raw('l.STATUS_DATA as "STATUS_DATA"')
            )
            ->where('l.ID', $id)
            ->whereRaw("(UPPER(l.STATUS_DATA) = 'ACTIVE' OR l.STATUS_DATA IS NULL)")
            ->first();
    
        if (!$kes) {
            return redirect()
                ->route('liabiliti-kes-list')
                ->with('error', 'Rekod tidak dijumpai.');
        }
    
        $jenisTuntutanAwam = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_NAME as "TUNTUTAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_NAME')
            ->get();

        $lokasiPenempatan = DB::connection('oracle')
            ->table('VEHICLE_LOKASI_PENEMPATAN')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('LOKASI_PENEMPATAN_NAME as "LOKASI_PENEMPATAN_NAME"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('LOKASI_PENEMPATAN_NAME')
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
    
        return view('pages.liabiliti.liabiliti-kes-edit', [
            'title' => 'Kemaskini Kes Liabiliti Awam',
            'kes' => $kes,
            'jenisTuntutanAwam' => $jenisTuntutanAwam,
            'departments' => $departments,
            'lokasiPenempatan' => $lokasiPenempatan,
        ]);
    }

    public function liabilitiKesUpdate(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|digits:4',
            'no_plat_harta_benda' => 'required|string|max:255',
            'tarikh_hantar_kes' => 'required|date',
            'tuntutan_awam_id' => 'required',
            'nama_penuntut' => 'required|string|max:255',
            'tarikh_kejadian' => 'required|date',
            'amaun_tuntutan' => 'required|numeric|min:0',
            'no_telefon' => 'nullable|string|max:50',
            'tarikh_offer_letter' => 'nullable|date',
            'lokasi_kejadian' => 'required|string|max:1000',
            'jabatan_kod' => 'required|string|max:50',
            'jabatan_nama' => 'nullable|string|max:255',
            'bahagian' => 'required|string|max:100',
            'daerah_kejadian' => 'required|string|max:50',
            'status_tuntutan' => 'required|string|in:DALAM PROSES,TINDAKAN ADJUSTER,SELESAI TANDATANGAN OFFER LETTER',
            'keputusan_kes' => 'nullable|string|in:LULUS,DITOLAK',
            'laporan_adjuster_insurans' => 'nullable|string',
        ], [
            'tahun.required' => 'Sila masukkan tahun.',
            'tahun.digits' => 'Tahun mestilah 4 digit.',
            'no_plat_harta_benda.required' => 'Sila masukkan no plat / harta benda.',
            'tarikh_hantar_kes.required' => 'Sila pilih tarikh hantar kes.',
            'tuntutan_awam_id.required' => 'Sila pilih jenis tuntutan.',
            'nama_penuntut.required' => 'Sila masukkan nama penuntut.',
            'tarikh_kejadian.required' => 'Sila pilih tarikh kejadian.',
            'amaun_tuntutan.required' => 'Sila masukkan amaun tuntutan.',
            'lokasi_kejadian.required' => 'Sila masukkan lokasi kejadian / alamat rumah.',
            'jabatan_kod.required' => 'Sila pilih jabatan.',
            'bahagian.required' => 'Sila pilih bahagian.',
            'daerah_kejadian.required' => 'Sila pilih daerah kejadian.',
            'status_tuntutan.required' => 'Sila pilih status tuntutan.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('LIABILITI_AWAM')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->route('liabiliti-kes-list')
                ->with('error', 'Rekod tidak dijumpai.');
        }
    
        $jenisTuntutan = DB::connection('oracle')
            ->table('TUNTUTAN_AWAM')
            ->where('ID', $request->tuntutan_awam_id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if (!$jenisTuntutan) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jenis tuntutan awam tidak dijumpai atau tidak aktif.');
        }
    
        $tuntutanName = $jenisTuntutan->TUNTUTAN_NAME
            ?? $jenisTuntutan->tuntutan_name
            ?? null;
    
        DB::connection('oracle')
            ->table('LIABILITI_AWAM')
            ->where('ID', $id)
            ->update([
                'TAHUN' => $request->tahun,
                'NO_PLAT_HARTA_BENDA' => strtoupper(trim($request->no_plat_harta_benda)),
                'TARIKH_HANTAR_KES' => $request->tarikh_hantar_kes,
    
                'TUNTUTAN_AWAM_ID' => $request->tuntutan_awam_id,
                'JENIS_TUNTUTAN' => $tuntutanName,
    
                'NAMA_PENUNTUT' => strtoupper(trim($request->nama_penuntut)),
                'TARIKH_KEJADIAN' => $request->tarikh_kejadian,
                'AMAUN_TUNTUTAN' => $request->amaun_tuntutan,
                'NO_TELEFON' => $request->no_telefon,
                'TARIKH_OFFER_LETTER' => $request->tarikh_offer_letter,
    
                'LOKASI_KEJADIAN' => $request->lokasi_kejadian,
                'JABATAN_KOD' => $request->jabatan_kod,
                'JABATAN_NAMA' => $request->jabatan_nama,
    
                'BAHAGIAN' => $request->bahagian,
                'DAERAH_KEJADIAN' => $request->daerah_kejadian,
    
                'STATUS_TUNTUTAN' => $request->status_tuntutan,
                'KEPUTUSAN_KES' => $request->keputusan_kes,
    
                'LAPORAN_ADJUSTER_INSURANS' => $request->laporan_adjuster_insurans,
    
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->route('liabiliti-kes-list')
            ->with('success', 'Kes liabiliti awam berjaya dikemaskini.');
    }

    public function liabilitiKesFile($id)
    {
        $kes = DB::connection('oracle')
            ->table('LIABILITI_AWAM')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('LAMPIRAN_FILE as "LAMPIRAN_FILE"')
            )
            ->where('ID', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();

        if (!$kes) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tidak dijumpai.');
        }

        $filePath = $kes->LAMPIRAN_FILE ?? $kes->lampiran_file ?? null;

        if (!$filePath || !Storage::disk('synology')->exists($filePath)) {
            return redirect()
                ->back()
                ->with('error', 'Fail lampiran tidak dijumpai.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $headers = [
            'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
        ];

        return Storage::disk('synology')->response(
            $filePath,
            basename($filePath),
            $headers,
            'inline'
        );
    }
    public function pihakKetigaTuntutanList()
    {
        $pihakKetigaTuntutan = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw('TUNTUTAN_PIHAK_KETIGA_NAME as "TUNTUTAN_PIHAK_KETIGA_NAME"'),
                DB::raw('CREATED_BY as "CREATED_BY"'),
                DB::raw('CREATED_AT as "CREATED_AT"'),
                DB::raw('UPDATED_BY as "UPDATED_BY"'),
                DB::raw('UPDATED_AT as "UPDATED_AT"'),
                DB::raw('STATUS_DATA as "STATUS_DATA"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->orderBy('TUNTUTAN_PIHAK_KETIGA_NAME')
            ->get();
    
        return view('pages.tuntutan.pihak-ketiga-tuntutan-list', [
            'title' => 'Senarai Tuntutan Pihak Ketiga',
            'pihakKetigaTuntutan' => $pihakKetigaTuntutan,
        ]);
    }
    
    public function pihakKetigaTuntutanAdd()
    {
        return view('pages.tuntutan.pihak-ketiga-tuntutan-add', [
            'title' => 'Tambah Tuntutan Pihak Ketiga',
        ]);
    }
    
    public function pihakKetigaTuntutanStore(Request $request)
    {
        $request->validate([
            'tuntutan_pihak_ketiga_name' => 'required|string|max:255',
        ], [
            'tuntutan_pihak_ketiga_name.required' => 'Sila masukkan tuntutan pihak ketiga.',
        ]);
    
        $name = strtoupper(trim($request->tuntutan_pihak_ketiga_name));
    
        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->whereRaw('UPPER(TRIM(TUNTUTAN_PIHAK_KETIGA_NAME)) = ?', [$name])
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Tuntutan pihak ketiga ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->insert([
                'TUNTUTAN_PIHAK_KETIGA_NAME' => $name,
                'CREATED_BY' => session('nostaff') ?? 'SYSTEM',
                'CREATED_AT' => now('Asia/Kuala_Lumpur'),
                'STATUS_DATA' => 'ACTIVE',
            ]);
    
        return redirect()
            ->route('pihak-ketiga-tuntutan-list')
            ->with('success', 'Tuntutan pihak ketiga berjaya ditambah.');
    }
    
    public function pihakKetigaTuntutanUpdate(Request $request, $id)
    {
        $request->validate([
            'tuntutan_pihak_ketiga_name' => 'required|string|max:255',
        ], [
            'tuntutan_pihak_ketiga_name.required' => 'Sila masukkan tuntutan pihak ketiga.',
        ]);
    
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tuntutan pihak ketiga tidak dijumpai.');
        }
    
        $name = strtoupper(trim($request->tuntutan_pihak_ketiga_name));
    
        $exists = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->whereRaw('UPPER(TRIM(TUNTUTAN_PIHAK_KETIGA_NAME)) = ?', [$name])
            ->where('ID', '<>', $id)
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->first();
    
        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Tuntutan pihak ketiga ini telah wujud.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->where('ID', $id)
            ->update([
                'TUNTUTAN_PIHAK_KETIGA_NAME' => $name,
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Tuntutan pihak ketiga berjaya dikemaskini.');
    }
    
    public function pihakKetigaTuntutanDelete($id)
    {
        $record = DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->where('ID', $id)
            ->first();
    
        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Rekod tuntutan pihak ketiga tidak dijumpai.');
        }
    
        DB::connection('oracle')
            ->table('TUNTUTAN_PIHAK_KETIGA')
            ->where('ID', $id)
            ->update([
                'STATUS_DATA' => 'DELETE',
                'UPDATED_BY' => session('nostaff') ?? 'SYSTEM',
                'UPDATED_AT' => now('Asia/Kuala_Lumpur'),
            ]);
    
        return redirect()
            ->back()
            ->with('success', 'Tuntutan pihak ketiga berjaya dipadam.');
    }
}