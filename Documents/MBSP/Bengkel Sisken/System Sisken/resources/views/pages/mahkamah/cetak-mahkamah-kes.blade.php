<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        h2 {
            margin-bottom: 4px;
        }

        .subtitle {
            margin-bottom: 18px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th {
            width: 32%;
            background: #f3f4f6;
            text-align: left;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin: 18px 0 8px;
        }
    </style>
</head>

<body>
    <h2>Maklumat Kes Mahkamah</h2>

    <div class="subtitle">
        Nombor Kes:
        {{ $kes->NO_KES_MAHKAMAH ?? $kes->no_kes_mahkamah ?? '-' }}
    </div>

    <div class="section-title">Maklumat Kes</div>

    <table>
        <tr>
            <th>Tahun</th>
            <td>{{ $kes->TAHUN ?? $kes->tahun ?? '-' }}</td>
        </tr>

        <tr>
            <th>Daerah Kejadian</th>
            <td>{{ $kes->DAERAH_KEJADIAN ?? $kes->daerah_kejadian ?? '-' }}</td>
        </tr>

        <tr>
            <th>No Kes Mahkamah</th>
            <td>{{ $kes->NO_KES_MAHKAMAH ?? $kes->no_kes_mahkamah ?? '-' }}</td>
        </tr>

        <tr>
            <th>Peguam Plaintif</th>
            <td>{{ $kes->PEGUAM_PLAINTIF ?? $kes->peguam_plaintif ?? '-' }}</td>
        </tr>

        <tr>
            <th>Peguam Mewakili MBSP</th>
            <td>{{ $kes->PEGUAM_MBSP ?? $kes->peguam_mbsp ?? '-' }}</td>
        </tr>

        <tr>
            <th>Nama Penuntut</th>
            <td>{{ $kes->NAMA_PENUNTUT ?? $kes->nama_penuntut ?? '-' }}</td>
        </tr>

        <tr>
            <th>No Kenderaan / Jenis</th>
            <td>{{ $kes->NO_KENDERAAN_JENIS ?? $kes->no_kenderaan_jenis ?? '-' }}</td>
        </tr>

        <tr>
            <th>Jenis Tuntutan</th>
            <td>{{ $kes->JENIS_TUNTUTAN ?? $kes->jenis_tuntutan ?? '-' }}</td>
        </tr>

        <tr>
            <th>Tarikh Bicara Seterusnya</th>
            <td>
                @php
                    $tarikhBicara = $kes->TARIKH_BICARA_SETERUSNYA ?? $kes->tarikh_bicara_seterusnya ?? null;
                @endphp

                {{ !empty($tarikhBicara)
                    ? \Carbon\Carbon::parse($tarikhBicara)->format('d/m/Y')
                    : '-'
                }}
            </td>
        </tr>

        <tr>
            <th>Status Perbicaraan</th>
            <td>{{ $kes->STATUS_PERBICARAAN ?? $kes->status_perbicaraan ?? '-' }}</td>
        </tr>

        <tr>
            <th>Keputusan Kes</th>
            <td>{{ $kes->KEPUTUSAN_KES ?? $kes->keputusan_kes ?? '-' }}</td>
        </tr>

        <tr>
            <th>Status Kes</th>
            <td>{{ $kes->STATUS_KES ?? $kes->status_kes ?? '-' }}</td>
        </tr>

        <tr>
            <th>Penyata Tuntutan</th>
            <td>{{ $kes->PENYATA_TUNTUTAN ?? $kes->penyata_tuntutan ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Maklumat Kenderaan / Insurans</div>

    <table>
        <tr>
            <th>No Plat Kenderaan</th>
            <td>{{ $kes->NO_PLAT ?? $kes->no_plat ?? '-' }}</td>
        </tr>

        <tr>
            <th>Syarikat Insurans</th>
            <td>{{ $kes->SYARIKAT_INSURANS ?? $kes->syarikat_insurans ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">Maklumat Jabatan / Pegawai</div>

    <table>
        <tr>
            <th>Jabatan Kod</th>
            <td>{{ $kes->JABATAN_KOD ?? $kes->jabatan_kod ?? '-' }}</td>
        </tr>

        <tr>
            <th>Jabatan</th>
            <td>{{ $kes->JABATAN_NAMA ?? $kes->jabatan_nama ?? '-' }}</td>
        </tr>

        <tr>
            <th>No Pekerja</th>
            <td>{{ $kes->NO_PEKERJA ?? $kes->no_pekerja ?? '-' }}</td>
        </tr>

        <tr>
            <th>Nama Pekerja MBSP</th>
            <td>{{ $kes->NAMA_STAF ?? $kes->nama_staf ?? '-' }}</td>
        </tr>

        <tr>
            <th>Jawatan</th>
            <td>{{ $kes->JAWATAN_STAF ?? $kes->jawatan_staf ?? '-' }}</td>
        </tr>
    </table>
</body>
</html>