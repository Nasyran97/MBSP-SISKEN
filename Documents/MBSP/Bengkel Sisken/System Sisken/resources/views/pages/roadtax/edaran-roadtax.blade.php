<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        h2 {
            text-align: center;
            font-size: 15px;
            margin-bottom: 18px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>
        SENARAI CUKAI JALAN {{ strtoupper($jabatan) }}
    
        @if(!empty($tempoh))
            UNTUK TEMPOH PEMBAHARUAN SEHINGGA {{ $tempoh }}
        @endif
    </h2>
    
<table>
    <thead>
        <tr>
            <th>BIL</th>
            <th>NO PLAT</th>
            <th>JENIS KENDERAAN</th>
            <th>LOKASI PENEMPATAN</th>
            <th>PENGAWAS KENDERAAN</th>
            <th>TAMAT CUKAI JALAN</th>
            <th>TAMAT INSURANS</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cars as $index => $car)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $car->no_plat ?? '-' }}</td>
                <td>{{ $car->jenis_kenderaan ?? '-' }}</td>
                <td>{{ $car->lokasi_penempatan ?? '-' }}</td>
                <td>{{ $car->pengawas_kenderaan ?? '-' }}</td>
                <td class="center">
                    {{ !empty($car->tarikh_tamat) ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('d/m/Y') : '-' }}
                </td>
                <td>
                    {{ !empty($car->tarikh_tamat_insurans)
                        ? \Carbon\Carbon::parse($car->tarikh_tamat_insurans)->format('d/m/Y')
                        : '-'
                    }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>