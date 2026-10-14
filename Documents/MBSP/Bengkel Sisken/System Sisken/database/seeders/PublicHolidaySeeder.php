<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicHolidaySeeder extends Seeder
{
    public function run(): void
    {
        // [date, name, scope, replacement_for, remarks]
        $rows = [
            ['2026-01-01', 'Tahun Baharu', 'NATIONAL'],
            ['2026-02-01', 'Hari Thaipusam', 'PENANG'],
            ['2026-02-02', 'Cuti Ganti Hari Thaipusam', 'PENANG', '2026-02-01'],
            ['2026-02-17', 'Tahun Baru Cina', 'NATIONAL'],
            ['2026-02-18', 'Tahun Baru Cina (Hari Kedua)', 'NATIONAL'],
            ['2026-03-07', 'Hari Nuzul Al-Quran', 'PENANG'],
            ['2026-03-21', 'Hari Raya Aidilfitri', 'NATIONAL'],
            ['2026-03-22', 'Hari Raya Aidilfitri (Hari Kedua)', 'NATIONAL'],
            ['2026-03-23', 'Cuti Ganti Hari Raya Aidilfitri', 'NATIONAL', '2026-03-22'],
            ['2026-05-01', 'Hari Pekerja', 'NATIONAL'],
            ['2026-05-27', 'Hari Raya Aidiladha', 'NATIONAL'],
            ['2026-05-31', 'Hari Wesak', 'NATIONAL'],
            ['2026-06-01', 'Hari Keputeraan Yang di-Pertuan Agong', 'NATIONAL'],
            ['2026-06-02', 'Cuti Ganti Hari Wesak', 'NATIONAL', '2026-05-31'],
            ['2026-06-17', 'Awal Muharram', 'NATIONAL'],
            ['2026-07-07', 'Hari Warisan Dunia George Town', 'PENANG'],
            ['2026-07-11', 'Hari Keputeraan Yang di-Pertua Negeri Pulau Pinang', 'PENANG'],
            ['2026-08-26', 'Maulidur Rasul', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2026-08-31', 'Hari Kebangsaan', 'NATIONAL'],
            ['2026-09-16', 'Hari Malaysia', 'NATIONAL'],
            ['2026-11-08', 'Deepavali', 'NATIONAL'],
            ['2026-11-09', 'Cuti Ganti Deepavali', 'NATIONAL', '2026-11-08'],
            ['2026-12-25', 'Hari Krismas', 'NATIONAL'],

            ['2027-01-01', 'Tahun Baharu', 'NATIONAL'],
            ['2027-01-22', 'Hari Thaipusam', 'PENANG'],
            ['2027-02-06', 'Tahun Baru Cina', 'NATIONAL'],
            ['2027-02-07', 'Tahun Baru Cina (Hari Kedua)', 'NATIONAL'],
            ['2027-02-08', 'Cuti Ganti Tahun Baru Cina', 'NATIONAL', '2027-02-07'],
            ['2027-02-24', 'Hari Nuzul Al-Quran', 'PENANG'],
            ['2027-03-10', 'Hari Raya Aidilfitri', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2027-03-11', 'Hari Raya Aidilfitri (Hari Kedua)', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2027-05-01', 'Hari Pekerja', 'NATIONAL'],
            ['2027-05-17', 'Hari Raya Aidiladha', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2027-05-20', 'Hari Wesak', 'NATIONAL'],
            ['2027-06-06', 'Awal Muharram', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2027-06-07', 'Hari Keputeraan Yang di-Pertuan Agong', 'NATIONAL'],
            ['2027-06-08', 'Cuti Ganti Awal Muharram', 'NATIONAL', '2027-06-06'],
            ['2027-07-07', 'Hari Warisan Dunia George Town', 'PENANG'],
            ['2027-07-10', 'Hari Keputeraan Yang di-Pertua Negeri Pulau Pinang', 'PENANG'],
            ['2027-08-15', 'Maulidur Rasul', 'NATIONAL', null, 'Tarikh bergantung pada penetapan anak bulan'],
            ['2027-08-16', 'Cuti Ganti Maulidur Rasul', 'NATIONAL', '2027-08-15'],
            ['2027-08-31', 'Hari Kebangsaan', 'NATIONAL'],
            ['2027-09-16', 'Hari Malaysia', 'NATIONAL'],
            ['2027-10-28', 'Deepavali', 'NATIONAL', null, 'Tarikh bergantung pada kalendar lunar'],
            ['2027-12-25', 'Hari Krismas', 'NATIONAL'],
        ];

        $db = DB::connection('oracle');

        foreach ($rows as $r) {
            $exists = $db->table('PUBLIC_HOLIDAY')
                ->whereRaw("TRUNC(HOLIDAY_DATE) = TO_DATE(?, 'YYYY-MM-DD')", [$r[0]])
                ->where('HOLIDAY_NAME', $r[1])
                ->exists();

            if ($exists) {
                continue;
            }

            $db->table('PUBLIC_HOLIDAY')->insert([
                'HOLIDAY_DATE' => DB::raw("TO_DATE('{$r[0]}', 'YYYY-MM-DD')"),
                'HOLIDAY_YEAR' => (int) substr($r[0], 0, 4),
                'HOLIDAY_NAME' => $r[1],
                'HOLIDAY_SCOPE' => $r[2],
                'IS_REPLACEMENT' => isset($r[3]) ? 'Y' : 'N',
                'REPLACEMENT_FOR_DATE' => isset($r[3]) ? DB::raw("TO_DATE('{$r[3]}', 'YYYY-MM-DD')") : null,
                'REMARKS' => $r[4] ?? null,
                'STATUS_DATA' => 'ACTIVE',
                'CREATED_BY' => 'SYSTEM',
            ]);
        }
    }
}
