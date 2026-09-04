@php
    $rowArray = (array) $kes;

    $normalized = [];
    foreach ($rowArray as $key => $value) {
        $normalized[strtoupper($key)] = $value;
    }

    $get = function (...$keys) use ($normalized) {
        foreach ($keys as $key) {
            $upperKey = strtoupper($key);

            if (array_key_exists($upperKey, $normalized)) {
                $value = $normalized[$upperKey];

                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return null;
    };

    $tarikhHantarKes = $get('TARIKH_HANTAR_KES');
    $tarikhKejadian = $get('TARIKH_KEJADIAN');
    $tarikhOfferLetter = $get('TARIKH_OFFER_LETTER');
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maklumat Kes Liabiliti Awam</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 22px;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #4b5563;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            vertical-align: top;
        }

        .label {
            width: 28%;
            background: #f9fafb;
            font-weight: bold;
            color: #374151;
        }

        .value {
            width: 72%;
        }

        .amount {
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Maklumat Kes Liabiliti Awam</h2>
        <p>No Plat / Harta Benda: {{ $get('NO_PLAT_HARTA_BENDA') ?? '-' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Maklumat Tuntutan</div>
        <table>
            <tr>
                <td class="label">Tahun</td>
                <td class="value">{{ $get('TAHUN') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No Plat / Harta Benda</td>
                <td class="value">{{ $get('NO_PLAT_HARTA_BENDA') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tarikh Hantar Kes</td>
                <td class="value">{{ !empty($tarikhHantarKes) ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Tuntutan</td>
                <td class="value">{{ $get('JENIS_TUNTUTAN') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Penuntut</td>
                <td class="value">{{ $get('NAMA_PENUNTUT') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tarikh Kejadian</td>
                <td class="value">{{ !empty($tarikhKejadian) ? \Carbon\Carbon::parse($tarikhKejadian)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Amaun Tuntutan</td>
                <td class="value amount">
                    RM {{ $get('AMAUN_TUNTUTAN') !== null ? number_format((float) $get('AMAUN_TUNTUTAN'), 2) : '0.00' }}
                </td>
            </tr>
            <tr>
                <td class="label">No Telefon</td>
                <td class="value">{{ $get('NO_TELEFON') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tarikh Offer Letter</td>
                <td class="value">{{ !empty($tarikhOfferLetter) ? \Carbon\Carbon::parse($tarikhOfferLetter)->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Lokasi Kejadian / Alamat Rumah</td>
                <td class="value">{{ $get('LOKASI_KEJADIAN') ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Maklumat Jabatan</div>
        <table>
            <tr>
                <td class="label">Jabatan Kod</td>
                <td class="value">{{ $get('JABATAN_KOD') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="value">{{ $get('JABATAN_NAMA') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bahagian</td>
                <td class="value">{{ $get('BAHAGIAN') ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Daerah Kejadian</td>
                <td class="value">{{ $get('DAERAH_KEJADIAN') ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Status Kes</div>
        <table>
            <tr>
                <td class="label">Status Tuntutan</td>
                <td class="value"><span class="badge">{{ $get('STATUS_TUNTUTAN') ?? '-' }}</span></td>
            </tr>
            <tr>
                <td class="label">Keputusan Kes</td>
                <td class="value"><span class="badge">{{ $get('KEPUTUSAN_KES') ?? '-' }}</span></td>
            </tr>
            <tr>
                <td class="label">Laporan Adjuster / Insurans</td>
                <td class="value">{{ $get('LAPORAN_ADJUSTER_INSURANS') ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Dokumen ini dijana melalui Sistem Liabiliti Awam MBSP.
    </div>
</body>
</html>