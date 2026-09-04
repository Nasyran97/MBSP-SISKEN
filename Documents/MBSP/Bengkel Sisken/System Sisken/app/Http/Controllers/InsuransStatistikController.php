<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuransStatistikController extends Controller
{
    public function statistikDataDisplay(Request $request)
    {
        $jenisStatistik = $request->input('jenis_statistik');

        $data = $this->getDefaultStatistikData($jenisStatistik);

        switch ($jenisStatistik) {
            case 'maklumat_kenderaan':
                $data = array_merge($data, $this->getMaklumatKenderaanData());
                break;

            case 'kemalangan_kenderaan_mbsp':
                $data = array_merge($data, $this->getKemalanganKenderaanMbspData());
                break;

            case 'cukai_jalan':
                // nanti kita buat function cukai jalan
                break;

            case 'tukar_milik_kenderaan':
                // nanti kita buat function tukar milik
                break;

            case 'tuntutan_cermin_pecah':
                // nanti kita buat function tuntutan cermin pecah
                break;

            case 'tuntutan_pokok_tumbang':
                // nanti kita buat function tuntutan pokok tumbang
                break;

            case 'tuntutan_jalan_raya_berlubang':
                // nanti kita buat function tuntutan jalan raya berlubang
                break;

            case 'laporan_prosiding_kes_mahkamah':
                // nanti kita buat function prosiding mahkamah
                break;
        }

        return view('pages.insurans-statistik-laporan.statistik-data-display', $data);
    }

    private function getDefaultStatistikData($jenisStatistik = null)
    {
        return [
            'title' => 'Statistik',
            'jenisStatistik' => $jenisStatistik,

            // Maklumat Kenderaan
            'jenisBahanBakarLabels' => [],
            'jenisBahanBakarData' => [],
            'tahunPendaftaranLabels' => [],
            'tahunPendaftaranData' => [],
            'jenisBadanLabels' => [],
            'jenisBadanData' => [],

            // Kemalangan Kenderaan MBSP
            'kemalanganTahunanLabels' => [],
            'kemalanganTahunanData' => [],
            'daerahKemalanganLabels' => [],
            'daerahKemalanganData' => [],
            'jenisKenderaanYearLabels' => [],
            'jenisKenderaanYearDatasets' => [],
            'jabatanKenderaanYearLabels' => [],
            'jabatanKenderaanYearDatasets' => [],
            'lokasiPenempatanYearLabels' => [],
            'lokasiPenempatanYearDatasets' => [],
            'jenisTuntutanPihakKetigaYearLabels' => [],
            'jenisTuntutanPihakKetigaYearDatasets' => [],
        ];
    }

    private function getMaklumatKenderaanData()
    {
        $jenisBahanBakarLabels = [];
        $jenisBahanBakarData = [];

        $tahunPendaftaranLabels = [];
        $tahunPendaftaranData = [];

        $jenisBadanLabels = [];
        $jenisBadanData = [];

        /*
        |--------------------------------------------------------------------------
        | 1. Jenis Bahan Bakar
        |--------------------------------------------------------------------------
        */
        $jenisBahanBakar = DB::connection('oracle')
            ->table('VEHICLE')
            ->select(
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_BAHAN_BAKAR)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->groupBy(DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_BAHAN_BAKAR)), ''), 'TIDAK DINYATAKAN')"))
            ->orderBy(DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_BAHAN_BAKAR)), ''), 'TIDAK DINYATAKAN')"))
            ->get();

        foreach ($jenisBahanBakar as $row) {
            $jenisBahanBakarLabels[] = $row->LABEL ?? $row->label ?? '-';
            $jenisBahanBakarData[] = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tahun Pendaftaran + Umur Kenderaan
        |--------------------------------------------------------------------------
        */
        $tahunPendaftaran = DB::connection('oracle')
            ->table('VEHICLE')
            ->select(
                DB::raw("
                    (
                        FLOOR(TO_NUMBER(TO_CHAR(TARIKH_PENDAFTARAN, 'YYYY')) / 10) * 10
                    ) || ' - ' || (
                        FLOOR(TO_NUMBER(TO_CHAR(TARIKH_PENDAFTARAN, 'YYYY')) / 10) * 10 + 9
                    ) as \"LABEL\"
                "),
                DB::raw('COUNT(*) as "JUMLAH"'),
                DB::raw("
                    FLOOR(TO_NUMBER(TO_CHAR(TARIKH_PENDAFTARAN, 'YYYY')) / 10) * 10 as \"SORT_YEAR\"
                ")
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereNotNull('TARIKH_PENDAFTARAN')
            ->groupBy(DB::raw("
                FLOOR(TO_NUMBER(TO_CHAR(TARIKH_PENDAFTARAN, 'YYYY')) / 10) * 10
            "))
            ->orderBy(DB::raw("
                FLOOR(TO_NUMBER(TO_CHAR(TARIKH_PENDAFTARAN, 'YYYY')) / 10) * 10
            "))
            ->get();

        $currentYear = now()->year;

        foreach ($tahunPendaftaran as $row) {
            $label = $row->LABEL ?? $row->label ?? '-';
            $jumlah = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);
            $sortYear = (int) ($row->SORT_YEAR ?? $row->sort_year ?? 0);

            $startYear = $sortYear;
            $endYear = $sortYear + 9;

            $minAge = max(0, $currentYear - $endYear);
            $maxAge = max(0, $currentYear - $startYear);

            $tahunPendaftaranLabels[] = $label . ' (' . $minAge . ' - ' . $maxAge . ' tahun)';
            $tahunPendaftaranData[] = $jumlah;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Jenis Badan - Top 15 + Lain-lain
        |--------------------------------------------------------------------------
        */
        $jenisBadan = DB::connection('oracle')
            ->table('VEHICLE')
            ->select(
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_BADAN)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->groupBy(DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_BADAN)), ''), 'TIDAK DINYATAKAN')"))
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get();

        $topLimit = 15;
        $othersTotal = 0;

        foreach ($jenisBadan as $index => $row) {
            $label = $row->LABEL ?? $row->label ?? '-';
            $jumlah = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);

            if ($index < $topLimit) {
                $jenisBadanLabels[] = $label;
                $jenisBadanData[] = $jumlah;
            } else {
                $othersTotal += $jumlah;
            }
        }

        if ($othersTotal > 0) {
            $jenisBadanLabels[] = 'LAIN-LAIN';
            $jenisBadanData[] = $othersTotal;
        }

        return [
            'jenisBahanBakarLabels' => $jenisBahanBakarLabels,
            'jenisBahanBakarData' => $jenisBahanBakarData,

            'tahunPendaftaranLabels' => $tahunPendaftaranLabels,
            'tahunPendaftaranData' => $tahunPendaftaranData,

            'jenisBadanLabels' => $jenisBadanLabels,
            'jenisBadanData' => $jenisBadanData,
        ];
    }

    private function getKemalanganKenderaanMbspData()
    {
        $kemalanganTahunanLabels = [];
        $kemalanganTahunanData = [];

        $daerahKemalanganLabels = [];
        $daerahKemalanganData = [];

        $jenisKenderaanYearLabels = [];
        $jenisKenderaanYearDatasets = [];

        $jabatanKenderaanYearLabels = [];
        $jabatanKenderaanYearDatasets = [];

        $lokasiPenempatanYearLabels = [];
        $lokasiPenempatanYearDatasets = [];

        $jenisTuntutanPihakKetigaYearLabels = [];
        $jenisTuntutanPihakKetigaYearDatasets = [];

        /*
        |--------------------------------------------------------------------------
        | Helper untuk chart by year
        |--------------------------------------------------------------------------
        */
        $buildYearDatasets = function ($rows) {
            $years = [];
            $categories = [];
            $matrix = [];
            $totals = [];

            foreach ($rows as $row) {
                $tahun = (string) ($row->TAHUN ?? $row->tahun ?? '');
                $label = (string) ($row->LABEL ?? $row->label ?? 'TIDAK DINYATAKAN');
                $jumlah = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);

                if ($tahun === '') {
                    continue;
                }

                $years[$tahun] = $tahun;
                $categories[$label] = $label;
                $matrix[$label][$tahun] = $jumlah;
                $totals[$label] = ($totals[$label] ?? 0) + $jumlah;
            }

            ksort($years);
            $yearLabels = array_values($years);

            uksort($categories, function ($a, $b) use ($totals) {
                return ($totals[$b] ?? 0) <=> ($totals[$a] ?? 0);
            });

            $datasets = [];

            foreach (array_keys($categories) as $label) {
                $data = [];

                foreach ($yearLabels as $tahun) {
                    $data[] = $matrix[$label][$tahun] ?? 0;
                }

                $datasets[] = [
                    'label' => $label,
                    'data' => $data,
                    'borderWidth' => 1,
                ];
            }

            return [$yearLabels, $datasets];
        };

        /*
        |--------------------------------------------------------------------------
        | 1. Pie Chart - Jumlah Kemalangan Mengikut Tahun
        |--------------------------------------------------------------------------
        */
        $kemalanganTahunan = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->select(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY') as \"TAHUN\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereNotNull('TARIKH_KEMALANGAN')
            ->groupBy(DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"))
            ->orderBy(DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"))
            ->get();

        foreach ($kemalanganTahunan as $row) {
            $kemalanganTahunanLabels[] = (string) ($row->TAHUN ?? $row->tahun ?? '-');
            $kemalanganTahunanData[] = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Column Chart - Jumlah Kemalangan Mengikut Daerah
        |--------------------------------------------------------------------------
        */
        $daerahKemalangan = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->select(
                DB::raw("NVL(NULLIF(TRIM(UPPER(DAERAH)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->groupBy(DB::raw("NVL(NULLIF(TRIM(UPPER(DAERAH)), ''), 'TIDAK DINYATAKAN')"))
            ->orderBy(DB::raw("NVL(NULLIF(TRIM(UPPER(DAERAH)), ''), 'TIDAK DINYATAKAN')"))
            ->get();

        foreach ($daerahKemalangan as $row) {
            $daerahKemalanganLabels[] = $row->LABEL ?? $row->label ?? '-';
            $daerahKemalanganData[] = (int) ($row->JUMLAH ?? $row->jumlah ?? 0);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Column Chart - Jenis Kenderaan by Year
        |--------------------------------------------------------------------------
        */
        $jenisKenderaanByYear = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->select(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY') as \"TAHUN\""),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_KENDERAAN)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereNotNull('TARIKH_KEMALANGAN')
            ->groupBy(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_KENDERAAN)), ''), 'TIDAK DINYATAKAN')")
            )
            ->orderBy(DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"))
            ->get();

        [$jenisKenderaanYearLabels, $jenisKenderaanYearDatasets] = $buildYearDatasets($jenisKenderaanByYear);

        /*
        |--------------------------------------------------------------------------
        | 4. Column Chart - Jabatan Kenderaan by Year
        |--------------------------------------------------------------------------
        */
        $jabatanKenderaanByYear = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->select(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY') as \"TAHUN\""),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JABATAN_KENDERAAN)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereNotNull('TARIKH_KEMALANGAN')
            ->groupBy(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JABATAN_KENDERAAN)), ''), 'TIDAK DINYATAKAN')")
            )
            ->orderBy(DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"))
            ->get();

        [$jabatanKenderaanYearLabels, $jabatanKenderaanYearDatasets] = $buildYearDatasets($jabatanKenderaanByYear);

        /*
        |--------------------------------------------------------------------------
        | 5. Column Chart - Lokasi Penempatan by Year
        |--------------------------------------------------------------------------
        | Join VEHICLE guna NO_PLAT.
        |--------------------------------------------------------------------------
        */
        $lokasiPenempatanByYear = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN as vk')
            ->leftJoin('VEHICLE as v', function ($join) {
                $join->on(
                    DB::raw('UPPER(TRIM(v.NO_PLAT))'),
                    '=',
                    DB::raw('UPPER(TRIM(vk.NO_PLAT))')
                );
            })
            ->select(
                DB::raw("TO_CHAR(vk.TARIKH_KEMALANGAN, 'YYYY') as \"TAHUN\""),
                DB::raw("NVL(NULLIF(TRIM(UPPER(v.LOKASI_PENEMPATAN)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(vk.STATUS_DATA) = 'ACTIVE' OR vk.STATUS_DATA IS NULL)")
            ->whereNotNull('vk.TARIKH_KEMALANGAN')
            ->groupBy(
                DB::raw("TO_CHAR(vk.TARIKH_KEMALANGAN, 'YYYY')"),
                DB::raw("NVL(NULLIF(TRIM(UPPER(v.LOKASI_PENEMPATAN)), ''), 'TIDAK DINYATAKAN')")
            )
            ->orderBy(DB::raw("TO_CHAR(vk.TARIKH_KEMALANGAN, 'YYYY')"))
            ->get();

        [$lokasiPenempatanYearLabels, $lokasiPenempatanYearDatasets] = $buildYearDatasets($lokasiPenempatanByYear);

        /*
        |--------------------------------------------------------------------------
        | 6. Column Chart - Jenis Tuntutan Pihak Ketiga by Year
        |--------------------------------------------------------------------------
        */
        $jenisTuntutanPihakKetigaByYear = DB::connection('oracle')
            ->table('VEHICLE_KEMALANGAN')
            ->select(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY') as \"TAHUN\""),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_TUNTUTAN_PIHAK_KETIGA)), ''), 'TIDAK DINYATAKAN') as \"LABEL\""),
                DB::raw('COUNT(*) as "JUMLAH"')
            )
            ->whereRaw("(UPPER(STATUS_DATA) = 'ACTIVE' OR STATUS_DATA IS NULL)")
            ->whereNotNull('TARIKH_KEMALANGAN')
            ->groupBy(
                DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"),
                DB::raw("NVL(NULLIF(TRIM(UPPER(JENIS_TUNTUTAN_PIHAK_KETIGA)), ''), 'TIDAK DINYATAKAN')")
            )
            ->orderBy(DB::raw("TO_CHAR(TARIKH_KEMALANGAN, 'YYYY')"))
            ->get();

        [$jenisTuntutanPihakKetigaYearLabels, $jenisTuntutanPihakKetigaYearDatasets] = $buildYearDatasets($jenisTuntutanPihakKetigaByYear);

        return [
            'kemalanganTahunanLabels' => $kemalanganTahunanLabels,
            'kemalanganTahunanData' => $kemalanganTahunanData,

            'daerahKemalanganLabels' => $daerahKemalanganLabels,
            'daerahKemalanganData' => $daerahKemalanganData,

            'jenisKenderaanYearLabels' => $jenisKenderaanYearLabels,
            'jenisKenderaanYearDatasets' => $jenisKenderaanYearDatasets,

            'jabatanKenderaanYearLabels' => $jabatanKenderaanYearLabels,
            'jabatanKenderaanYearDatasets' => $jabatanKenderaanYearDatasets,

            'lokasiPenempatanYearLabels' => $lokasiPenempatanYearLabels,
            'lokasiPenempatanYearDatasets' => $lokasiPenempatanYearDatasets,

            'jenisTuntutanPihakKetigaYearLabels' => $jenisTuntutanPihakKetigaYearLabels,
            'jenisTuntutanPihakKetigaYearDatasets' => $jenisTuntutanPihakKetigaYearDatasets,
        ];
    }
}