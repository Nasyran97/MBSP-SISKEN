<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpeedbizLesenController extends Controller
{
    /**
     * Retrieve license data.
     */
    public function getLicenseData(Request $request)
    {
        $AUID = $request->input('auid');
        $records = DB::connection('oracle_speedbiz')->table('applications as a')
            ->join(DB::raw("(select max(id) as max_id,id_application from application_1 where deleted_at is null group by id_application) a1"), 
                'a1.id_application', '=', 'a.id')
            ->join(DB::raw("(select max(id) as max_id,id_application_1 from Premises where deleted_at is null group by id_application_1) px"), 
                'px.id_application_1', '=', 'a1.max_id')
            ->join('premises as p', 'p.id', '=', 'px.max_id')
            ->join(DB::raw("(select max(id) as max_id,id_application from licences where deleted_at is null group by id_application) ll"), 
                'll.id_application', '=', 'a.id')
            ->join('licences as l', 'l.id', '=', 'll.max_id')
            ->join('applicants as app', 'app.id', '=', 'a.id_applicant')
            ->join('organizations as o', 'o.id', '=', 'a.id_organization')
            ->join(DB::raw("(select max(id) as max_id,id_application from application_activities where deleted_at is null group by id_application) aax"), 
                'aax.id_application', '=', 'a.id')
            ->join('application_activities as aa', 'aa.id', '=', 'aax.max_id')
            ->join('Activity_Type_Businesses as atb', 'aa.id_activity_type_business', '=', 'atb.id')
            ->join('activities as ac', 'aa.id_activity', '=', 'ac.id')
            ->join('New_Postcodes as np', 'p.postcode', '=', 'np.Postcode')
            ->join('dun as du', 'np.id_dun', '=', 'du.id')
            ->join('Parliaments as pr', 'du.Parliament_Id', '=', 'pr.id')
            ->join(DB::raw("(select max(id) as max_id,id_reference from bills where deleted_at is null group by id_reference) bx"), 
                'bx.Id_Reference', '=', 'a.id')
            ->join('bills as b', 'b.id', '=', 'bx.max_id')
            ->where('l.cd_status', '=', 'A') // Only AKTIF status
            ->where('p.id_daerah', '=', 703)
            ->where('b.Cd_Reference_Type', '=', 'A')
            ->where('p.auid_number', $AUID)
            ->whereRaw("l.Dt_End = (SELECT MAX(L2.Dt_End) FROM licences L2 WHERE L2.id_application = l.id_application)") // Latest tarikh_tamat
            ->select([
                'o.dt_registration_start as tarikh_sah_pendaftaran',
                DB::raw("'AKTIF' as status_lesen"), // Directly setting the value since we are filtering only 'A'
                'l.reference_no as no_rujukan_lesen',
                'Aa.Activity_Description as keterangan_lesen',
                'p.auid_number as AUID',
                'p.no_lot as no_premis',
                'p.address_1 as alamat',
                'p.postcode as poskod',
                'du.name as dun',
                'App.Full_Name as nama_pemegang_lesen',
                'O.Name as nama_syarikat',
                'o.phone as no_telefon',
                DB::raw("CASE 
                            WHEN p.id_daerah = 701 THEN 'SPT'
                            WHEN p.id_daerah = 702 THEN 'SPU'
                            WHEN p.id_daerah = 703 THEN 'SPS'
                            ELSE ''
                        END AS daerah"),
                'l.dt_start as tarikh_mula',
                'L.Dt_End as tarikh_tamat',
                'A.Approved_At as tarikh_lulus',
                DB::raw("CASE 
                            WHEN p.Is_In_Scheme = 1 THEN 'YA'
                            WHEN p.Is_In_Scheme = 0 THEN 'TIDAK'
                            ELSE ''
                        END AS jenis_skim"),
                'pr.name as parlimen',
                'Atb.Cd_Business_Sector',
                DB::raw("CASE 
                            WHEN Atb.Cd_Business_Sector = 1 THEN 'AM'
                            WHEN Atb.Cd_Business_Sector = 2 THEN 'INDUSTRI'
                            ELSE ''
                        END AS jenis_perniagaan"),
                'B.No_Amount as harga_lesen'
            ])
            ->get();


        return response()->json($records);
    }
}
