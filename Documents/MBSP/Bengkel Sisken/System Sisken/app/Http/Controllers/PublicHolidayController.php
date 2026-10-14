<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class PublicHolidayController extends Controller
{
    public function index()
    {
        $year = (int) request('year', date('Y'));

        $holidays = DB::connection('oracle')
            ->table('PUBLIC_HOLIDAY')
            ->select(
                DB::raw('ID as "ID"'),
                DB::raw("TO_CHAR(HOLIDAY_DATE, 'YYYY-MM-DD') as \"HOLIDAY_DATE\""),
                DB::raw('HOLIDAY_NAME as "HOLIDAY_NAME"'),
                DB::raw('HOLIDAY_SCOPE as "HOLIDAY_SCOPE"'),
                DB::raw('IS_REPLACEMENT as "IS_REPLACEMENT"'),
                DB::raw("TO_CHAR(REPLACEMENT_FOR_DATE, 'YYYY-MM-DD') as \"REPLACEMENT_FOR_DATE\""),
                DB::raw('REMARKS as "REMARKS"')
            )
            ->where('HOLIDAY_YEAR', $year)
            ->where('STATUS_DATA', 'ACTIVE')
            ->orderBy('HOLIDAY_DATE')
            ->get()
            ->map(function ($row) {
                // Oracle driver may return column names in upper or lower case
                $h = array_change_key_case((array) $row, CASE_LOWER);

                return [
                    'date' => $h['holiday_date'],
                    'name' => $h['holiday_name'],
                    'scope' => $h['holiday_scope'],
                    'replacement' => ($h['is_replacement'] ?? 'N') === 'Y',
                    'replacement_for' => $h['replacement_for_date'] ?? null,
                    'remarks' => $h['remarks'] ?? null,
                ];
            })
            ->values();

        return view('pages.umum.kalendar-cuti', [
            'title' => 'Kalendar Cuti Umum',
            'year' => $year,
            'holidays' => $holidays,
        ]);
    }
}
