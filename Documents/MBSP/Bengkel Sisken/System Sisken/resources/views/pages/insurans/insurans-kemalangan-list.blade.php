@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Kemalangan Insurans' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai rekod kemalangan dan tuntutan insurans kenderaan.
            </p>
        </div>

        <div>
            <a href="{{ route('insurans-data-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Data
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Kemalangan Insurans">
        <div class="p-1 overflow-x-auto">
            <table id="kemalanganTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Tahun</th>
                        <th>Tarikh Hantar Kes</th>
                        <th>No Plat</th>
                        <th>Tarikh Kejadian</th>
                        <th>Jenis Kenderaan</th>
                        <th>NCD Semasa</th>
                        <th>Jabatan</th>
                        <th>Bahagian</th>
                        <th>No Plat Pihak Ketiga</th>
                        <th>Jenis Kenderaan Pihak Ketiga</th>
                        <th>Jenis Tuntutan Pihak Ketiga</th>
                        <th class="text-center">Fail</th>
                        <th>Status Semasa Kes</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($kemalangan as $index => $row)
                        @php
                            $id = data_get($row, 'ID') ?? data_get($row, 'id');

                            $tarikhKejadian = data_get($row, 'TARIKH_KEMALANGAN')
                                ?? data_get($row, 'tarikh_kemalangan');

                            $tarikhHantarKes = data_get($row, 'TARIKH_HANTAR_KES')
                                ?? data_get($row, 'tarikh_hantar_kes');

                            $tahun = !empty($tarikhKejadian)
                                ? \Carbon\Carbon::parse($tarikhKejadian)->format('Y')
                                : '-';

                            $noPlat = data_get($row, 'NO_PLAT_KEMALANGAN')
                                ?? data_get($row, 'no_plat_kemalangan')
                                ?? data_get($row, 'NO_PLAT_VEHICLE')
                                ?? data_get($row, 'no_plat_vehicle')
                                ?? '-';

                            $jenisKenderaan = data_get($row, 'JENIS_KENDERAAN')
                                ?? data_get($row, 'jenis_kenderaan')
                                ?? '-';

                            $ncdSemasa = data_get($row, 'NCD_SEMASA')
                                ?? data_get($row, 'ncd_semasa')
                                ?? '-';

                                $jabatan = data_get($row, 'JABATAN_KENDERAAN')
                                    ?? data_get($row, 'jabatan_kenderaan')
                                    ?? data_get($row, 'DESC_JABATAN_STAF')
                                    ?? data_get($row, 'desc_jabatan_staff')
                                    ?? data_get($row, 'KOD_JABATAN_STAF')
                                    ?? data_get($row, 'kod_jabatan_staff')
                                    ?? '-';

                            $bahagian = data_get($row, 'BAHAGIAN')
                                ?? data_get($row, 'bahagian')
                                ?? '-';

                            $noPlatPihakKetiga = data_get($row, 'NO_PLAT_PIHAK_KETIGA')
                                ?? data_get($row, 'no_plat_pihak_ketiga')
                                ?? '-';

                            $jenisKenderaanPihakKetiga = data_get($row, 'JENIS_KENDERAAN_PIHAK_KETIGA')
                                ?? data_get($row, 'jenis_kenderaan_pihak_ketiga')
                                ?? '-';

                            $jenisTuntutanPihakKetiga = data_get($row, 'JENIS_TUNTUTAN_PIHAK_KETIGA')
                                ?? data_get($row, 'jenis_tuntutan_pihak_ketiga')
                                ?? '-';

                            $lampiranFile = data_get($row, 'LAMPIRAN_FILE')
                                ?? data_get($row, 'lampiran_file')
                                ?? null;

                            $statusKes = strtoupper(trim(
                                data_get($row, 'STATUS_KES')
                                ?? data_get($row, 'status_kes')
                                ?? ''
                            ));

                            $statusClass = match($statusKes) {
                                'SELESAI' => 'status-active',
                                'DALAM PROSES' => 'status-warning',
                                'DITOLAK' => 'status-expired',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>{{ $tahun }}</td>

                            <td>
                                {{ !empty($tarikhHantarKes)
                                    ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td>
                                {{ !empty($tarikhKejadian)
                                    ? \Carbon\Carbon::parse($tarikhKejadian)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisKenderaan }}
                            </td>

                            <td class="text-center">
                                {{ $ncdSemasa !== '-' ? $ncdSemasa . '%' : '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $jabatan }}
                            </td>

                            <td class="cell-text">
                                {{ $bahagian }}
                            </td>

                            <td class="font-semibold">
                                {{ $noPlatPihakKetiga }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisKenderaanPihakKetiga }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisTuntutanPihakKetiga }}
                            </td>

                            <td>
                                @if($statusKes)
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusKes }}
                                    </span>
                                @else
                                    <span class="status-badge status-default">
                                        TIADA
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if(!empty($lampiranFile))
                                    <a href="{{ route('insurans-kemalangan-lampiran', $id) }}"
                                        target="_blank"
                                        class="file-view-btn">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tiada</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn info-btn"
                                        onclick="openKemalanganInfoModal('kemalangan-info-modal-{{ $id }}')"
                                        title="Maklumat">
                                        ℹ️
                                    </button>
                            
                                    <button type="button"
                                        class="action-btn edit-btn"
                                        onclick="openKemalanganEditModal('kemalangan-edit-modal-{{ $id }}')"
                                        title="Edit Status Kes">
                                        ✏️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="15" class="text-center py-4 text-gray-500">
                                Tiada data kemalangan insurans.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    {{-- MODAL INFO KEMALANGAN --}}
    @foreach($kemalangan as $row)
    @php
        $rowArray = (array) $row;

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

        $id = $get('ID');

        $noPlat = $get(
            'NO_PLAT_KEMALANGAN',
            'NO_PLAT_VEHICLE',
            'NO_PLAT'
        ) ?? '-';

        $tarikhKemalangan = $get('TARIKH_KEMALANGAN');
        $tarikhHantarKes = $get('TARIKH_HANTAR_KES');
        $createdAt = $get('CREATED_AT');
        $updatedAt = $get('UPDATED_AT');
        $lampiranFile = $get('LAMPIRAN_FILE', 'lampiran_file');
    @endphp

    <div id="kemalangan-info-modal-{{ $id }}" class="kemalangan-modal hidden">
        <div class="kemalangan-modal-overlay" onclick="closeKemalanganInfoModal('kemalangan-info-modal-{{ $id }}')"></div>

        <div class="kemalangan-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Kemalangan / Tuntutan Insurans
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeKemalanganInfoModal('kemalangan-info-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <div class="detail-section">
                <h4>Maklumat Utama</h4>
                <div class="detail-grid">
                    <div>
                        <span>No Plat</span>
                        <strong>{{ $noPlat }}</strong>
                    </div>
            
                    <div>
                        <span>Jabatan Staf</span>
                        <strong>{{ $get('DESC_JABATAN_STAF', 'desc_jabatan_staff') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>No Pekerja</span>
                        <strong>{{ $get('NO_PEKERJA', 'no_pekerja') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Nama</span>
                        <strong>{{ $get('NAMA', 'nama') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Jawatan</span>
                        <strong>{{ $get('JAWATAN', 'jawatan') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Jabatan Kenderaan</span>
                        <strong>
                            {{
                                $get(
                                    'JABATAN_KENDERAAN',
                                    'jabatan_kenderaan',
                                    'JABATAN_VEHICLE',
                                    'jabatan_vehicle'
                                ) ?? '-'
                            }}
                        </strong>
                    </div>
            
                    <div>
                        <span>Jenis Kenderaan</span>
                        <strong>{{ $get('JENIS_KENDERAAN', 'jenis_kenderaan') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>NCD Semasa</span>
                        <strong>{{ $get('NCD_SEMASA', 'ncd_semasa') ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Maklumat Insurans / Tuntutan</h4>
                <div class="detail-grid">
                    <div>
                        <span>Baik Pulih Bengkel</span>
                        <strong>{{ $get('BAIK_PULIH_BENGKEL', 'baik_pulih_bengkel') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Jenis Tuntutan Insurans</span>
                        <strong>{{ $get('JENIS_TUNTUTAN_INSURANS', 'jenis_tuntutan_insurans') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Bahagian</span>
                        <strong>{{ $get('BAHAGIAN', 'bahagian') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Daerah</span>
                        <strong>{{ $get('DAERAH', 'daerah') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Tarikh Kemalangan</span>
                        <strong>
                            {{ !empty($tarikhKemalangan) ? \Carbon\Carbon::parse($tarikhKemalangan)->format('d/m/Y') : '-' }}
                        </strong>
                    </div>
            
                    <div>
                        <span>Lokasi Kemalangan</span>
                        <strong>{{ $get('LOKASI_KEMALANGAN', 'lokasi_kemalangan') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Kos Baik Pulih</span>
                        <strong>RM {{ $get('KOS_BAIK_PULIH', 'kos_baik_pulih') ?? '0.00' }}</strong>
                    </div>
            
                    <div>
                        <span>Kos Tuntutan Pihak Ketiga</span>
                        <strong>RM {{ $get('KOS_TUNTUTAN_PIHAK_KETIGA', 'kos_tuntutan_pihak_ketiga') ?? '0.00' }}</strong>
                    </div>
            
                    <div class="detail-full">
                        <span>Keterangan Kemalangan</span>
                        <strong>{{ $get('KETERANGAN_KEMALANGAN', 'keterangan_kemalangan') ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Maklumat Pihak Ketiga</h4>
                <div class="detail-grid">
                    <div>
                        <span>Nama Penuntut</span>
                        <strong>{{ $get('NAMA_PENUNTUT', 'nama_penuntut') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>No Plat Pihak Ketiga</span>
                        <strong>{{ $get('NO_PLAT_PIHAK_KETIGA', 'no_plat_pihak_ketiga') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Jenis Kenderaan / Harta Benda</span>
                        <strong>{{ $get('JENIS_KENDERAAN_PIHAK_KETIGA', 'jenis_kenderaan_pihak_ketiga') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Jenis Tuntutan Pihak Ketiga</span>
                        <strong>{{ $get('JENIS_TUNTUTAN_PIHAK_KETIGA', 'jenis_tuntutan_pihak_ketiga') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>No Telefon Pihak Ketiga</span>
                        <strong>{{ $get('NO_TELEFON_PIHAK_KETIGA', 'no_telefon_pihak_ketiga') ?? '-' }}</strong>
                    </div>
            
                    <div class="detail-full">
                        <span>Kerosakan Pihak Ketiga</span>
                        <strong>{{ $get('KEROSAKAN_PIHAK_KETIGA', 'kerosakan_pihak_ketiga') ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>Maklumat Kes</h4>
                <div class="detail-grid">
                    <div>
                        <span>Tarikh Hantar Kes</span>
                        <strong>
                            {{ !empty($tarikhHantarKes) ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y') : '-' }}
                        </strong>
                    </div>
            
                    <div>
                        <span>Tuntutan Gantirugi</span>
                        <strong>{{ $get('TUNTUTAN_GANTIRUGI', 'tuntutan_gantirugi') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Status Kes</span>
                        <strong>{{ $get('STATUS_KES', 'status_kes') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Keputusan Kes Trafik</span>
                        <strong>{{ $get('KEPUTUSAN_KES_TRAFIK', 'keputusan_kes_trafik') ?? '-' }}</strong>
                    </div>
            
                    <div class="detail-full">
                        <span>Kemajuan Semasa</span>
                        <strong>{{ $get('KEMAJUAN_SEMASA', 'kemajuan_semasa') ?? '-' }}</strong>
                    </div>
            
                    <div class="detail-full">
                        <span>Laporan Adjuster / Insurans</span>
                        <strong>{{ $get('LAPORAN_ADJUSTER_INSURANS', 'laporan_adjuster_insurans') ?? '-' }}</strong>
                    </div>

                    <div class="detail-full">
                        <span>Fail Lampiran</span>
                    
                        <strong>
                            @if(!empty($lampiranFile))
                                <a href="{{ route('insurans-kemalangan-lampiran', $id) }}"
                                    target="_blank"
                                    class="file-view-btn">
                                    Lihat
                                </a>
                            @else
                                -
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
            
            {{-- <div class="detail-section">
                <h4>Maklumat Sistem</h4>
                <div class="detail-grid">
                    <div>
                        <span>Created By</span>
                        <strong>{{ $get('CREATED_BY', 'created_by') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Created At</span>
                        <strong>{{ !empty($createdAt) ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Updated By</span>
                        <strong>{{ $get('UPDATED_BY', 'updated_by') ?? '-' }}</strong>
                    </div>
            
                    <div>
                        <span>Updated At</span>
                        <strong>{{ !empty($updatedAt) ? \Carbon\Carbon::parse($updatedAt)->format('d/m/Y H:i') : '-' }}</strong>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
    @endforeach

    {{-- MODAL EDIT STATUS KES --}}
    @foreach($kemalangan as $row)
    @php
        $rowArray = (array) $row;

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

        $id = $get('ID');

        $noPlat = $get(
            'NO_PLAT_KEMALANGAN',
            'NO_PLAT_VEHICLE',
            'NO_PLAT'
        ) ?? '-';

        $statusKes = $get('STATUS_KES') ?? '';
        $keputusanKesTrafik = $get('KEPUTUSAN_KES_TRAFIK') ?? '';
        $kemajuanSemasa = $get('KEMAJUAN_SEMASA') ?? '';
        $laporanAdjuster = $get('LAPORAN_ADJUSTER_INSURANS') ?? '';
        $lampiranFile = $get('LAMPIRAN_FILE', 'lampiran_file');
    @endphp

    <div id="kemalangan-edit-modal-{{ $id }}" class="kemalangan-modal hidden">
        <div class="kemalangan-modal-overlay" onclick="closeKemalanganEditModal('kemalangan-edit-modal-{{ $id }}')"></div>

        <div class="kemalangan-modal-card kemalangan-edit-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Kemaskini Status Kes
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeKemalanganEditModal('kemalangan-edit-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('insurans-kemalangan-update-status', $id) }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Status Semasa Kes
                        </label>

                        <select name="status_kes"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="SELESAI" {{ strtoupper($statusKes) == 'SELESAI' ? 'selected' : '' }}>
                                Selesai
                            </option>
                            <option value="DALAM PROSES" {{ strtoupper($statusKes) == 'DALAM PROSES' ? 'selected' : '' }}>
                                Dalam Proses
                            </option>
                            <option value="DITOLAK" {{ strtoupper($statusKes) == 'DITOLAK' ? 'selected' : '' }}>
                                Ditolak
                            </option>
                            <option value="TIADA TINDAKAN LANJUT" {{ strtoupper($statusKes) == 'TIADA TINDAKAN LANJUT' ? 'selected' : '' }}>
                                Tiada Tindakan Lanjut
                            </option>
                        </select>
                    </div>

                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Keputusan Kes Trafik
                        </label>

                        <select name="keputusan_kes_trafik"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="BERSALAH" {{ strtoupper($keputusanKesTrafik) == 'BERSALAH' ? 'selected' : '' }}>
                                Bersalah
                            </option>
                            <option value="TIDAK BERSALAH" {{ strtoupper($keputusanKesTrafik) == 'TIDAK BERSALAH' ? 'selected' : '' }}>
                                Tidak Bersalah
                            </option>
                            <option value="DALAM PROSES" {{ strtoupper($keputusanKesTrafik) == 'DALAM PROSES' ? 'selected' : '' }}>
                                Dalam Proses
                            </option>
                        </select>
                    </div>

                    <div class="col-span-12">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kemajuan Semasa
                        </label>

                        <textarea name="kemajuan_semasa"
                            rows="4"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan kemajuan semasa">{{ $kemajuanSemasa }}</textarea>
                    </div>

                    <div class="col-span-12">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Laporan Adjuster / Insurans
                        </label>

                        <textarea name="laporan_adjuster_insurans"
                            rows="4"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan laporan adjuster / insurans">{{ $laporanAdjuster }}</textarea>
                    </div>

                    <div class="col-span-12">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Fail Lampiran
                        </label>
                    
                        @if(!empty($lampiranFile))
                            <div class="mb-2">
                                <a href="{{ route('insurans-kemalangan-lampiran', $id) }}"
                                    target="_blank"
                                    class="file-view-btn">
                                    Lihat Fail Semasa
                                </a>
                            </div>
                        @endif
                    
                        <input type="file"
                            name="lampiran_file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                    
                        <p class="mt-1 text-xs text-gray-500">
                            Biarkan kosong jika tidak mahu tukar fail. Format: PDF, JPG, JPEG, PNG. Maksimum 5MB.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium"
                        onclick="closeKemalanganEditModal('kemalangan-edit-modal-{{ $id }}')">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

<style>
    .user-table {
        width: 100% !important;
        min-width: 2480px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .user-table thead th {
        background: #f8f9fb;
        color: #344054;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: 14px 16px;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
        text-align: left;
        vertical-align: middle;
    }

    .user-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f4;
        vertical-align: middle;
        color: #101828;
    }

    .user-table tbody tr:hover td {
        background: #fafbfc;
    }

    .user-table th:nth-child(1),
    .user-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: 90px !important;
        min-width: 90px;
        max-width: 90px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(3),
    .user-table td:nth-child(3),
    .user-table th:nth-child(5),
    .user-table td:nth-child(5) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        white-space: nowrap;
    }

    .user-table th:nth-child(6),
    .user-table td:nth-child(6) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: normal;
    }

    .user-table th:nth-child(7),
    .user-table td:nth-child(7) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(8),
    .user-table td:nth-child(8) {
        width: 280px !important;
        min-width: 280px;
        max-width: 280px;
        white-space: normal;
    }

    .user-table th:nth-child(9),
    .user-table td:nth-child(9) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: normal;
    }

    .user-table th:nth-child(10),
    .user-table td:nth-child(10) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: nowrap;
    }

    .user-table th:nth-child(11),
    .user-table td:nth-child(11) {
        width: 250px !important;
        min-width: 250px;
        max-width: 250px;
        white-space: normal;
    }

    .user-table th:nth-child(12),
    .user-table td:nth-child(12) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(13),
    .user-table td:nth-child(13) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(14),
    .user-table td:nth-child(14) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(15),
    .user-table td:nth-child(15) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .status-active {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .status-warning {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-expired {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }

    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e7ec;
        background: #fff;
        color: #344054;
        text-decoration: none;
        transition: all .2s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #bae6fd;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }
    .kemalangan-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .kemalangan-modal.hidden {
        display: none;
    }

    .kemalangan-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .kemalangan-modal-card {
        position: relative;
        z-index: 1;
        width: min(1200px, calc(100% - 40px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #fff;
        border-radius: 18px;
        padding: 32px 36px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }

    .kemalangan-edit-card {
        width: min(850px, calc(100% - 40px));
    }

    .modal-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #344054;
        font-size: 26px;
        line-height: 1;
    }

    .detail-section {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #eef1f4;
    }

    .detail-section:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    .detail-section h4 {
        font-size: 15px;
        font-weight: 800;
        color: #1d2939;
        margin-bottom: 14px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-grid div {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .detail-grid span {
        display: block;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .detail-grid strong {
        display: block;
        color: #101828;
        font-size: 14px;
        font-weight: 700;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .detail-full {
        grid-column: span 2 / span 2;
    }

    .file-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 14px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .file-view-btn:hover {
        background: #dbeafe;
    }
    
    .swal2-container {
        z-index: 9999999 !important;
        position: fixed !important;
        inset: 0 !important;
    }

    .swal2-backdrop-show {
        background: rgba(15, 23, 42, 0.65) !important;
    }

    .swal2-popup {
        z-index: 10000000 !important;
    }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    const kemalanganTable = $('#kemalanganTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px' },
            { width: '90px' },
            { width: '150px' },
            { width: '130px' },
            { width: '150px' },
            { width: '180px' },
            { width: '120px' },
            { width: '280px' },
            { width: '180px' },
            { width: '180px' },
            { width: '250px' },
            { width: '230px' },
            { width: '170px' },
            { width: '130px', orderable: false },
            { width: '130px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            info: "Paparan _START_ hingga _END_ daripada _TOTAL_ rekod",
            infoEmpty: "Tiada rekod",
            infoFiltered: "(ditapis daripada _MAX_ jumlah rekod)",
            paginate: {
                first: "Pertama",
                last: "Akhir",
                next: "Seterusnya",
                previous: "Sebelumnya"
            }
        },

        initComplete: function () {
            this.api().columns.adjust();
        }
    });

    setTimeout(function () {
        kemalanganTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        kemalanganTable.columns.adjust();
    });
});

function openKemalanganInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeKemalanganInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openKemalanganEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeKemalanganEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.kemalangan-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});
</script>
@endpush

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berjaya',
        text: @json(session('success')),
        confirmButtonText: 'OK',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Ralat',
        text: @json(session('error')),
        confirmButtonText: 'OK',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    });
});
</script>
@endif

@endsection