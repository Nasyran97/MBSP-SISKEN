@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Tambah data insurans kenderaan.
            </p>
        </div>
    </div>

    <x-common.component-card title="Tambah Data Insurans">
        <form method="POST" action="{{ route('insurans-data-add-store') }}" id="insuransForm" enctype="multipart/form-data">
            @csrf

            {{-- Maklumat Utama --}}
            <div class="form-section">
                <h4 class="section-title">Maklumat Utama</h4>

                <div class="grid grid-cols-12 gap-4">
                    {{-- No Plat --}}
                    <div class="col-span-6">
                        <label for="vehicle_id" class="mb-2 block text-sm font-medium text-gray-700">
                            No Plat <span class="text-red-500">*</span>
                        </label>

                        <select name="vehicle_id" id="vehicle_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            required>
                            <option value="" selected disabled>-- Sila Pilih Kenderaan --</option>

                            @foreach($vehicles as $v)
                                @php
                                    $vehicleId = data_get($v, 'VEHICLE_ID')
                                        ?? data_get($v, 'vehicle_id')
                                        ?? data_get($v, 'ID')
                                        ?? data_get($v, 'id');

                                    $noPlat = data_get($v, 'NO_PLAT')
                                        ?? data_get($v, 'no_plat')
                                        ?? '';

                                    $jabatan = data_get($v, 'JABATAN')
                                        ?? data_get($v, 'jabatan')
                                        ?? '';

                                    $jenisKenderaan = data_get($v, 'JENIS_KENDERAAN')
                                        ?? data_get($v, 'jenis_kenderaan')
                                        ?? '';

                                    $currentNcd = data_get($v, 'CURRENT_NCD')
                                        ?? data_get($v, 'current_ncd')
                                        ?? '';

                                    $lokasiPenempatan = data_get($v, 'LOKASI_PENEMPATAN')
                                        ?? data_get($v, 'lokasi_penempatan')
                                        ?? '';
                                @endphp

                                <option value="{{ $vehicleId }}"
                                    data-no-plat="{{ $noPlat }}"
                                    data-jabatan="{{ $jabatan }}"
                                    data-jenis-kenderaan="{{ $jenisKenderaan }}"
                                    data-current-ncd="{{ $currentNcd }}"
                                    data-lokasi-penempatan="{{ $lokasiPenempatan }}">
                                    {{ $noPlat ?: '-' }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="no_plat" id="no_plat">
                    </div>

                    {{-- Jabatan --}}
                    <div class="col-span-6">
                        <label for="department" class="mb-2 block text-sm font-medium text-gray-700">
                            Jabatan <span class="text-red-500">*</span>
                        </label>

                        <select name="kod_jabatan_staff" id="department"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            required>
                            <option value="" selected disabled>-- Sila Pilih Jabatan --</option>

                            @foreach($departments as $dept)
                                @php
                                    $code = $dept['CODE']
                                        ?? $dept['KODJABATAN']
                                        ?? $dept['kodjabatan']
                                        ?? '';

                                    $description = $dept['DESCRIPTION']
                                        ?? $dept['DESCJABATAN']
                                        ?? $dept['descjabatan']
                                        ?? '';
                                @endphp

                                <option value="{{ $code }}" data-description="{{ $description }}">
                                    {{ $description }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="desc_jabatan_staff" id="desc_jabatan_staff">
                    </div>

                    {{-- Nama Staf / Pemandu --}}
                    <div class="col-span-6">
                        <label for="nostaff" class="mb-2 block text-sm font-medium text-gray-700">
                            No Pekerja / Pemandu <span class="text-red-500">*</span>
                        </label>

                        <select name="no_pekerja" id="nostaff"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-50"
                            disabled
                            required>
                            <option value="" selected disabled>-- Sila Pilih Jabatan Dahulu --</option>
                        </select>
                    </div>

                    {{-- No Pekerja Display --}}
                    <div class="col-span-6">
                        <label for="no_pekerja_display" class="mb-2 block text-sm font-medium text-gray-700">
                            No Pekerja
                        </label>

                        <input type="text"
                            id="no_pekerja_display"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm"
                            readonly>
                    </div>
                </div>
            </div>

            {{-- Maklumat Auto --}}
            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Auto</h4>

                <div class="grid grid-cols-12 gap-4">
                    {{-- Jabatan Kenderaan --}}
                    <div class="col-span-6">
                        <label for="jabatan_kenderaan" class="mb-2 block text-sm font-medium text-gray-700">
                            Jabatan Kenderaan
                        </label>

                        <input type="text"
                            name="jabatan_kenderaan"
                            id="jabatan_kenderaan"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm"
                            readonly>
                    </div>

                    {{-- Jenis Kenderaan --}}
                    <div class="col-span-6">
                        <label for="jenis_kenderaan" class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis Kenderaan
                        </label>

                        <input type="text"
                            name="jenis_kenderaan"
                            id="jenis_kenderaan"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm"
                            readonly>
                    </div>

                    {{-- Nama --}}
                    <div class="col-span-6">
                        <label for="nama" class="mb-2 block text-sm font-medium text-gray-700">
                            Nama
                        </label>

                        <input type="text"
                            name="nama"
                            id="nama"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm"
                            readonly>
                    </div>

                    {{-- Jawatan --}}
                    <div class="col-span-6">
                        <label for="jawatan" class="mb-2 block text-sm font-medium text-gray-700">
                            Jawatan
                        </label>

                        <input type="text"
                            name="jawatan"
                            id="jawatan"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm"
                            readonly>
                    </div>
                </div>
            </div>
    
            {{-- Maklumat Insurans / Tuntutan --}}
            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Insurans / Tuntutan</h4>

                <div class="grid grid-cols-12 gap-4">

                    {{-- NCD --}}
                    <div class="col-span-4">
                        <label for="current_ncd">NCD Semasa (%)</label>
                        <input type="number"
                            id="current_ncd"
                            step="0.01"
                            name="ncd"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            readonly
                            placeholder="NCD semasa akan dipaparkan">
                    </div>

                    {{-- Baik Pulih Di Bengkel --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Baik Pulih Di Bengkel
                        </label>
                        <select name="baik_pulih_bengkel"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="YA">Ya</option>
                            <option value="TIDAK">Tidak</option>
                        </select>
                    </div>

                    {{-- Jenis Tuntutan Insurans --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis Tuntutan
                        </label>
                        <select name="jenis_tuntutan_insurans"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="OD">OD</option>
                            <option value="OD-KFK">OD-KFK</option>
                            <option value="TOTAL LOST">Total Lost</option>
                            <option value="WINDSCREEN">Windscreen</option>
                            <option value="TIADA">Tiada</option>
                        </select>
                    </div>

                    {{-- Bahagian --}}
                    <div class="col-span-8">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Bahagian
                        </label>
                    
                        <input type="text"
                            name="lokasi_penempatan"
                            id="lokasi_penempatan"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Auto daripada kenderaan"
                            readonly>
                    </div>

                    {{-- Daerah --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Daerah
                        </label>
                        <select name="daerah"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="SPU">SPU</option>
                            <option value="SPT">SPT</option>
                            <option value="SPS">SPS</option>
                        </select>
                    </div>

                    {{-- Lokasi Kemalangan --}}
                    <div class="col-span-8">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Lokasi Kemalangan
                        </label>
                        <input type="text"
                            name="lokasi_kemalangan"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan lokasi kemalangan">
                    </div>

                    {{-- Tarikh Kemalangan --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Tarikh Kemalangan
                        </label>
                        <input type="text"
                            name="tarikh_kemalangan"
                            class="roadtax-date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Pilih tarikh">
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-span-12">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Keterangan
                        </label>
                        <textarea name="keterangan_kemalangan"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan keterangan"></textarea>
                    </div>

                    {{-- Kos Baik Pulih --}}
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kos Baik Pulih (RM)
                        </label>
                        <input type="number"
                            step="0.01"
                            name="kos_baik_pulih"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="0.00">
                    </div>

                    {{-- Kos Tuntutan Pihak Ketiga --}}
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kos Tuntutan Pihak Ketiga (RM)
                        </label>
                        <input type="number"
                            step="0.01"
                            name="kos_tuntutan_pihak_ketiga"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            {{-- Maklumat Pihak Ketiga --}}
            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Pihak Ketiga</h4>

                <div class="grid grid-cols-12 gap-4">

                    {{-- Nama Penuntut --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Nama Penuntut
                        </label>
                        <input type="text"
                            name="nama_penuntut"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan nama penuntut">
                    </div>

                    {{-- No Plat Pihak Ketiga --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            No Plat /
                        </label>
                        <input type="text"
                            name="no_plat_pihak_ketiga"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Contoh: PXX1234">
                    </div>

                    {{-- Jenis Kenderaan Pihak Ketiga --}}
                    <div class="col-span-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis Kenderaan / Harta Benda
                        </label>
                        <input type="text"
                            name="jenis_kenderaan_pihak_ketiga"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Contoh: Kereta / Motorsikal / Pagar">
                    </div>

                    {{-- Kerosakan Pihak Ketiga --}}
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kerosakan Pihak Ketiga
                        </label>
                        <textarea name="kerosakan_pihak_ketiga"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan butiran kerosakan"></textarea>
                    </div>

                    {{-- Jenis Tuntutan Pihak Ketiga --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Jenis Tuntutan
                        </label>

                        <select name="jenis_tuntutan_pihak_ketiga"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>

                            @foreach($tuntutanPihakKetiga as $row)
                                @php
                                    $tuntutanName = $row->TUNTUTAN_PIHAK_KETIGA_NAME ?? $row->tuntutan_pihak_ketiga_name ?? '';
                                @endphp

                                <option value="{{ $tuntutanName }}"
                                    {{ old('jenis_tuntutan_pihak_ketiga') == $tuntutanName ? 'selected' : '' }}>
                                    {{ $tuntutanName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- No Telefon Pihak Ketiga --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            No Telefon Pihak Ketiga
                        </label>
                        <input type="text"
                            name="no_telefon_pihak_ketiga"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Contoh: 0123456789">
                    </div>
                </div>
            </div>

            {{-- Maklumat Kes --}}
            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Kes</h4>

                <div class="grid grid-cols-12 gap-4">

                    {{-- Tarikh Hantar Kes --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Tarikh Hantar Kes
                        </label>
                        <input type="text"
                    name="tarikh_hantar_kes"
                    class="roadtax-date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                    placeholder="Pilih tarikh">
                    </div>

                    {{-- Tuntutan Gantirugi --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Tuntutan Gantirugi
                        </label>
                        <select name="tuntutan_gantirugi"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="INSURANS">Insurans</option>
                            <option value="BAYARAN GANTIRUGI">Bayaran Gantirugi</option>
                            <option value="TUNTUTAN MAHKAMAH">Tuntutan Mahkamah</option>
                        </select>
                    </div>

                    {{-- Status Kes --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Status Kes
                        </label>
                        <select name="status_kes"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="SELESAI">Selesai</option>
                            <option value="DALAM PROSES">Dalam Proses</option>
                            <option value="DITOLAK">Ditolak</option>
                            <option value="TIADA TINDAKAN LANJUT">Tiada Tindakan Lanjut</option>
                        </select>
                    </div>

                    {{-- Keputusan Kes Trafik --}}
                    <div class="col-span-3">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Keputusan Kes Trafik
                        </label>
                        <select name="keputusan_kes_trafik"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                            <option value="">-- Sila Pilih --</option>
                            <option value="BERSALAH">Bersalah</option>
                            <option value="TIDAK BERSALAH">Tidak Bersalah</option>
                            <option value="DALAM PROSES">Dalam Proses</option>
                        </select>
                    </div>

                    {{-- Kemajuan Semasa --}}
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kemajuan Semasa
                        </label>
                        <textarea name="kemajuan_semasa"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan kemajuan semasa"></textarea>
                    </div>

                    {{-- Laporan Adjuster / Insurans --}}
                    <div class="col-span-6">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Laporan Adjuster / Insurans
                        </label>
                        <textarea name="laporan_adjuster_insurans"
                            rows="3"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Masukkan laporan adjuster / insurans"></textarea>
                    </div>

                    <div class="col-span-12">
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Lampiran Kes
                        </label>
                    
                        <input type="file"
                            name="lampiran_file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                    
                        <p class="mt-1 text-xs text-gray-500">
                            Format dibenarkan: PDF, JPG, JPEG, PNG. Maksimum 5MB.
                        </p>
                    </div>
                </div>
            </div>
            {{-- Button Submit --}}
            <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                <button type="reset"
                    class="px-6 py-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold">
                    Reset
                </button>

                <button type="submit"
                    class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<style>
    .form-section {
    border: 1px solid #e4e7ec;
    border-radius: 16px;
    padding: 20px;
    background: #fff;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #101828;
    margin-bottom: 16px;
}

.form-cell-label {
    width: 13%;
    background: #f2f4f7;
    border: 1px solid #d0d5dd;
    padding: 10px 12px;
    font-weight: 700;
    color: #344054;
    vertical-align: middle;
    white-space: nowrap;
}

.form-cell-input {
    background: #ffffff;
    border: 1px solid #d0d5dd;
    padding: 8px;
    vertical-align: middle;
}

.form-section-row {
    background: #eef2f6;
    border: 1px solid #d0d5dd;
    padding: 10px 12px;
    font-weight: 800;
    color: #101828;
}

.form-input-table {
    width: 100%;
    min-height: 42px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 14px;
    color: #101828;
    background: #fff;
    outline: none;
}

.form-input-table:focus {
    border-color: #84caff;
    box-shadow: 0 0 0 3px rgba(45, 114, 255, 0.12);
}

.form-input-table[readonly] {
    background: #f9fafb;
    color: #475467;
}

    .readonly-field {
        background: #f9fafb;
        color: #475467;
    }

    .helper-text {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        font-weight: 500;
    }

    .helper-loading {
        color: #1d4ed8;
    }

    .helper-error {
        color: #b42318;
    }

    .helper-success {
        color: #027a48;
    }

    .btn-cancel,
    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-cancel {
        background: #f2f4f7;
        color: #344054;
    }

    .btn-submit {
        background: #2563eb;
        color: #fff;
    }

    .btn-submit:hover {
        background: #1d4ed8;
    }

    @media (max-width: 768px) {
        .grid.grid-cols-12 > div {
            grid-column: span 12 / span 12 !important;
        }
    }

    .swal2-container {
        z-index: 9999999 !important;
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
    }

    .swal2-backdrop-show {
        background: rgba(15, 23, 42, 0.65) !important;
    }

    .swal2-popup {
        z-index: 10000000 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.getElementById('department');
        const staffSelect = document.getElementById('nostaff');
        const vehicleSelect = document.getElementById('vehicle_id');
    
        const descJabatanInput = document.getElementById('desc_jabatan_staff');
    
        const noPekerjaDisplay = document.getElementById('no_pekerja_display');
        const namaInput = document.getElementById('nama');
        const jawatanInput = document.getElementById('jawatan');
    
        const noPlatInput = document.getElementById('no_plat');
        const jabatanKenderaanInput = document.getElementById('jabatan_kenderaan');
        const jenisKenderaanInput = document.getElementById('jenis_kenderaan');
        const currentNcdInput = document.getElementById('current_ncd');
        const lokasiPenempatanInput = document.getElementById('lokasi_penempatan');
    
        let staffTom = null;
        let vehicleTom = null;

        if (typeof flatpickr !== 'undefined') {
            flatpickr(".roadtax-date-picker", {
                dateFormat: "Y-m-d",
                allowInput: true,
                position: "above",
                appendTo: document.body
            });
        }
    
        function initStaffSearch() {
            if (staffTom) {
                staffTom.destroy();
                staffTom = null;
            }
    
            if (typeof TomSelect !== 'undefined' && staffSelect) {
                staffTom = new TomSelect('#nostaff', {
                    create: false,
                    allowEmptyOption: true,
                    placeholder: 'Cari nama staf...',
                    maxOptions: 5000,
                    searchField: ['text'],
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    onChange: function(value) {
                        const selectedOption = staffSelect.querySelector(`option[value="${value}"]`);
    
                        noPekerjaDisplay.value = selectedOption ? (selectedOption.dataset.nostaff || '') : '';
                        namaInput.value = selectedOption ? (selectedOption.dataset.nama || '') : '';
                        jawatanInput.value = selectedOption ? (selectedOption.dataset.jawatan || '') : '';
                    }
                });
            }
        }
    
        function initVehicleSearch() {
            if (vehicleTom) {
                vehicleTom.destroy();
                vehicleTom = null;
            }
    
            if (typeof TomSelect !== 'undefined' && vehicleSelect) {
                vehicleTom = new TomSelect('#vehicle_id', {
                    create: false,
                    allowEmptyOption: true,
                    placeholder: 'Cari no plat...',
                    maxOptions: 5000,
                    searchField: ['text'],
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    onChange: function(value) {
                        const selectedOption = vehicleSelect.querySelector(`option[value="${value}"]`);

                        const noPlat = selectedOption ? (selectedOption.dataset.noPlat || '') : '';
                        const jabatan = selectedOption ? (selectedOption.dataset.jabatan || '') : '';
                        const jenisKenderaan = selectedOption ? (selectedOption.dataset.jenisKenderaan || '') : '';
                        const currentNcd = selectedOption ? (selectedOption.dataset.currentNcd || '') : '';
                        const lokasiPenempatan = selectedOption ? (selectedOption.dataset.lokasiPenempatan || '') : '';

                        if (noPlatInput) noPlatInput.value = noPlat;
                        if (jabatanKenderaanInput) jabatanKenderaanInput.value = jabatan;
                        if (jenisKenderaanInput) jenisKenderaanInput.value = jenisKenderaan;
                        if (currentNcdInput) currentNcdInput.value = currentNcd;
                        if (lokasiPenempatanInput) lokasiPenempatanInput.value = lokasiPenempatan;
                    }
                });
            }
        }
    
        function clearStaffFields() {
            noPekerjaDisplay.value = '';
            namaInput.value = '';
            jawatanInput.value = '';
        }
    
        function loadStaffByDepartment(departmentCode) {
            if (staffTom) {
                staffTom.destroy();
                staffTom = null;
            }
    
            staffSelect.innerHTML = '<option value="">-- Loading Nama Staf --</option>';
            staffSelect.disabled = true;
            clearStaffFields();
    
            if (!departmentCode) {
                staffSelect.innerHTML = '<option value="" selected disabled>-- Sila Pilih Jabatan Dahulu --</option>';
                staffSelect.disabled = true;
                return;
            }
    
            fetch("{{ route('insurans.staff-by-department') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    kodjabatanstaff: departmentCode
                })
            })
            .then(response => response.json())
            .then(data => {
                staffSelect.innerHTML = '<option value="" selected disabled>-- Sila Pilih Nama Staf --</option>';
    
                const staffs = data.data ?? data.staffs ?? [];
    
                if (data.status && Array.isArray(staffs) && staffs.length > 0) {
                    staffs.forEach(staff => {
                        const noStaff = staff.NOSTAFF ?? '';
                        const namaStaff = staff.NAMASTAFF ?? '';
                        const jawatanStaff = staff.JAWATANSTAFF ?? '';
    
                        if (!noStaff) return;
    
                        const option = document.createElement('option');
                        option.value = noStaff;
                        option.textContent = noStaff + ' - ' + namaStaff;
                        option.dataset.nostaff = noStaff;
                        option.dataset.nama = namaStaff;
                        option.dataset.jawatan = jawatanStaff;
    
                        staffSelect.appendChild(option);
                    });
    
                    staffSelect.disabled = false;
                    initStaffSearch();
                } else {
                    staffSelect.innerHTML = '<option value="">-- Tiada Staf Dijumpai --</option>';
                    staffSelect.disabled = true;
                    clearStaffFields();
                }
            })
            .catch(() => {
                staffSelect.innerHTML = '<option value="">-- Ralat Semasa Ambil Data Staf --</option>';
                staffSelect.disabled = true;
                clearStaffFields();
            });
        }
    
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const departmentCode = this.value;
    
                descJabatanInput.value = departmentCode && selectedOption
                    ? (selectedOption.dataset.description || '')
                    : '';
    
                loadStaffByDepartment(departmentCode);
            });
        }
    
        initVehicleSearch();
    });
</script>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berjaya',
        text: @json(session('success')),
        confirmButtonText: 'OK'
    });
});
</script>
@endif

@endsection